<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\TransactionModel;
use App\Models\AdjustmentModel;
use App\Models\TripModel;
use App\Models\PeriodModel;
use App\Models\GroupMemberModel;
use Myth\Auth\Models\UserModel;

class Transactions extends BaseController
{
    protected $transactionModel;
    protected $adjustmentModel;
    protected $tripModel;
    protected $periodModel;
    protected $groupMemberModel;
    protected $userModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->adjustmentModel = new AdjustmentModel();
        $this->tripModel = new TripModel();
        $this->periodModel = new PeriodModel();
        $this->groupMemberModel = new GroupMemberModel();
        $this->userModel = new UserModel();
    }

    /**
     * Check if user is a member of the group of a trip
     */
    protected function checkTripAccess(int $tripId): ?array
    {
        $trip = $this->tripModel->find($tripId);
        if (!$trip) return null;

        return $this->groupMemberModel->where('group_id', $trip['group_id'])
                                       ->where('user_id', user_id())
                                       ->first();
    }

    /**
     * List transaksi dengan filter trip & periode
     */
    public function index()
    {
        $userId = user_id();

        // 1. Ambil semua trip yang bisa diakses user
        $availableTrips = $this->tripModel->select('trips.*, groups.name as group_name')
                                          ->join('groups', 'groups.id = trips.group_id')
                                          ->join('group_members', 'group_members.group_id = groups.id')
                                          ->where('group_members.user_id', $userId)
                                          ->findAll();

        // --- Session preference: simpan/restore trip & periode terakhir ---
        $session = session();

        if ($this->request->getGet('reset') !== null) {
            $session->remove('txn_last_trip_id');
            $session->remove('txn_last_period_id');
            $session->remove('txn_last_group_id');
            return redirect()->to('backend/transactions');
        }

        $selectedTripId   = $this->request->getGet('trip_id');
        $selectedPeriodId = $this->request->getGet('period_id');
        $selectedGroupId  = $this->request->getGet('group_id');
        $fromGet          = ($this->request->getGet('trip_id') !== null) || ($this->request->getGet('group_id') !== null);

        // Jika tidak ada GET param, coba restore dari session
        if (!$fromGet) {
            $selectedTripId   = $session->get('txn_last_trip_id');
            $selectedPeriodId = $session->get('txn_last_period_id');
            $selectedGroupId  = $session->get('txn_last_group_id');
        }

        // Fallback: gunakan trip pertama jika masih kosong dan tidak ada group terpilih
        if (empty($selectedTripId) && empty($selectedGroupId) && !empty($availableTrips)) {
            $selectedTripId = $availableTrips[0]['id'];
        }

        $transactions      = [];
        $selectedTrip      = null;
        $selectedGroup     = null;
        $periods           = [];
        $openPeriods       = [];
        $groupMembers      = [];
        $currentMembership = null;

        if (!empty($selectedTripId)) {
            // 2. Verifikasi akses ke trip terpilih
            $currentMembership = $this->checkTripAccess((int)$selectedTripId);
            if (!$currentMembership) {
                return redirect()->to('backend/transactions')->with('error', 'Anda tidak memiliki akses ke trip ini.');
            }

            $selectedTrip = $this->tripModel->find($selectedTripId);
            
            // 3. Dapatkan list periode untuk filter sidebar (semua periode)
            $periods = $this->periodModel->where('trip_id', $selectedTripId)->orderBy('created_at', 'ASC')->findAll();

            // 3b. Periode yang hanya berstatus 'open' untuk dropdown form transaksi
            $openPeriods = array_filter($periods, fn($p) => ($p['status'] ?? 'open') === 'open');

            // 4. Query transaksi
            $transQuery = $this->transactionModel->select('transactions.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as paid_by_name, COALESCE(NULLIF(creator.fullname, \'\'), creator.username) as creator_name, trip_periods.label as period_label')
                                                 ->join('users', 'users.id = transactions.paid_by')
                                                 ->join('users creator', 'creator.id = transactions.created_by')
                                                 ->join('trip_periods', 'trip_periods.id = transactions.period_id', 'left')
                                                 ->where('transactions.trip_id', $selectedTripId);

            if (!empty($selectedPeriodId)) {
                $transQuery->where('transactions.period_id', $selectedPeriodId);
            }

            $transactions = $transQuery->orderBy('transactions.date', 'DESC')->findAll();

            // Tambahkan data detail adjustments untuk transaksi bertipe individual
            foreach ($transactions as &$t) {
                if ($t['type'] === 'individual') {
                    $t['adjustments'] = $this->adjustmentModel->select('transaction_adjustments.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as username')
                                                              ->join('users', 'users.id = transaction_adjustments.target_user_id')
                                                              ->where('transaction_adjustments.transaction_id', $t['id'])
                                                              ->findAll();
                }
            }

            // 5. Ambil semua anggota grup untuk input form modal
            $groupMembers = $this->groupMemberModel->select('group_members.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as username')
                                                   ->join('users', 'users.id = group_members.user_id')
                                                   ->where('group_members.group_id', $selectedTrip['group_id'])
                                                   ->findAll();

            // --- Simpan preferensi terakhir ke session ---
            $session->set('txn_last_trip_id',   $selectedTripId);
            $session->set('txn_last_period_id', $selectedPeriodId ?: null);
            $session->set('txn_last_group_id',  null);
        } else if (!empty($selectedGroupId)) {
            // Verifikasi akses user ke group terpilih
            $currentMembership = $this->groupMemberModel->where('group_id', $selectedGroupId)
                                                         ->where('user_id', $userId)
                                                         ->first();
            if (!$currentMembership) {
                return redirect()->to('backend/transactions')->with('error', 'Anda tidak memiliki akses ke grup ini.');
            }

            $selectedGroup = $this->groupMemberModel->select('groups.*')
                                                   ->join('groups', 'groups.id = group_members.group_id')
                                                   ->where('groups.id', $selectedGroupId)
                                                   ->first();

            $tripsInGroup = $this->tripModel->where('group_id', $selectedGroupId)->findAll();
            $tripIds = array_column($tripsInGroup, 'id');

            if (!empty($tripIds)) {
                $transQuery = $this->transactionModel->select('transactions.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as paid_by_name, COALESCE(NULLIF(creator.fullname, \'\'), creator.username) as creator_name, trip_periods.label as period_label, trips.name as trip_name')
                                                     ->join('users', 'users.id = transactions.paid_by')
                                                     ->join('users creator', 'creator.id = transactions.created_by')
                                                     ->join('trip_periods', 'trip_periods.id = transactions.period_id', 'left')
                                                     ->join('trips', 'trips.id = transactions.trip_id')
                                                     ->whereIn('transactions.trip_id', $tripIds);
                $transactions = $transQuery->orderBy('transactions.date', 'DESC')->findAll();

                foreach ($transactions as &$t) {
                    if ($t['type'] === 'individual') {
                        $t['adjustments'] = $this->adjustmentModel->select('transaction_adjustments.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as username')
                                                                  ->join('users', 'users.id = transaction_adjustments.target_user_id')
                                                                  ->where('transaction_adjustments.transaction_id', $t['id'])
                                                                  ->findAll();
                    }
                }
            }

            $groupMembers = $this->groupMemberModel->select('group_members.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as username')
                                                   ->join('users', 'users.id = group_members.user_id')
                                                   ->where('group_members.group_id', $selectedGroupId)
                                                   ->findAll();

            $session->set('txn_last_trip_id',   null);
            $session->set('txn_last_period_id', null);
            $session->set('txn_last_group_id',  $selectedGroupId);
        }

        $calculationResult = null;
        if (!empty($selectedPeriodId)) {
            $calculationEngine = new \App\Libraries\CalculationEngine();
            try {
                $calculationResult = $calculationEngine->calculatePeriod((int)$selectedPeriodId);
            } catch (\Exception $e) {
                // Biarkan null jika gagal
            }
        } else if (!empty($selectedTripId) || !empty($selectedGroupId)) {
            // Hitung agregasi summary untuk seluruh periode (Pilih Semua) di bawah kegiatan atau grup terpilih
            $totalTransactions = 0;
            $totalShared = 0;
            $totalIndividual = 0;
            foreach ($transactions as $t) {
                $amt = (int)$t['amount'];
                $totalTransactions += $amt;
                if ($t['type'] === 'shared') {
                    $totalShared += $amt;
                } else {
                    $totalIndividual += $amt;
                }
            }
            $numMembers = count($groupMembers);
            $splitRata = $numMembers > 0 ? (int)round($totalShared / $numMembers) : 0;

            $calculationResult = [
                'is_all_periods' => true,
                'summary' => [
                    'total_transactions' => $totalTransactions,
                    'total_shared'       => $totalShared,
                    'total_individual'   => $totalIndividual,
                    'split_rata'         => $splitRata,
                ]
            ];
        }

        // Kumpulkan semua periode per trip untuk rendering sisi klien (filter tanpa reload)
        $allPeriodsJson = [];
        $filterHierarchy = [];

        $allPeriods = [];
        if (!empty($availableTrips)) {
            $allTripIds = array_column($availableTrips, 'id');
            $allPeriods = $this->periodModel
                               ->select('id, label, status, trip_id')
                               ->whereIn('trip_id', $allTripIds)
                               ->orderBy('created_at', 'ASC')
                               ->findAll();
        }

        $periodsByTrip = [];
        foreach ($allPeriods as $p) {
            $periodsByTrip[$p['trip_id']][] = $p;
            $allPeriodsJson[$p['trip_id']][] = [
                'id' => $p['id'],
                'label' => $p['label'],
                'status' => $p['status']
            ];
        }

        foreach ($availableTrips as $at) {
            $groupId = (int)$at['group_id'];
            $groupName = $at['group_name'];
            $tripId = (int)$at['id'];
            $tripName = $at['name'];

            if (!isset($filterHierarchy[$groupId])) {
                $filterHierarchy[$groupId] = [
                    'name' => $groupName,
                    'trips' => []
                ];
            }
            $filterHierarchy[$groupId]['trips'][$tripId] = [
                'name' => $tripName,
                'periods' => $periodsByTrip[$tripId] ?? []
            ];

            if (!isset($allPeriodsJson[$tripId])) {
                $allPeriodsJson[$tripId] = [];
            }
        }

        $data = [
            'pageTitle'         => 'Catatan Transaksi',
            'availableTrips'    => $availableTrips,
            'selectedTripId'    => $selectedTripId,
            'selectedTrip'      => $selectedTrip,
            'selectedGroupId'   => $selectedGroupId ?? null,
            'selectedGroup'     => $selectedGroup ?? null,
            'selectedPeriodId'  => $selectedPeriodId,
            'periods'           => $periods,
            'openPeriods'       => $openPeriods ?? [],
            'transactions'      => $transactions,
            'groupMembers'      => $groupMembers,
            'currentMembership' => $currentMembership,
            'calculationResult' => $calculationResult,
            'allPeriodsJson'    => json_encode($allPeriodsJson),
            'user'              => user(),
            'filterHierarchy'   => $filterHierarchy,
        ];

        return view('backend/transactions/index', $data);
    }

    /**
     * AJAX: Ambil daftar periode berdasarkan trip_id
     */
    public function getPeriodsAjax()
    {
        $tripId = (int)$this->request->getGet('trip_id');
        if (!$tripId) {
            return $this->response->setJSON(['periods' => []]);
        }

        $membership = $this->checkTripAccess($tripId);
        if (!$membership) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak']);
        }

        $periods = $this->periodModel
            ->select('id, label, status')
            ->where('trip_id', $tripId)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        return $this->response->setJSON(['periods' => $periods]);
    }

    /**
     * Simpan transaksi baru (Shared atau Individual)
     */
    public function store()
    {
        $rules = [
            'trip_id'       => 'required|numeric',
            'period_id'     => 'permit_empty',
            'date'          => 'required|valid_date[Y-m-d]',
            'description'   => 'required|min_length[3]|max_length[255]',
            'amount'        => 'required|numeric|greater_than[0]',
            'paid_by'       => 'required|numeric',
            'type'          => 'required|in_list[shared,individual]',
            'receipt_image' => 'permit_empty|is_image[receipt_image]|max_size[receipt_image,5120]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tripId   = (int)$this->request->getPost('trip_id');
        $periodId = $this->request->getPost('period_id');
        $amount   = (int)$this->request->getPost('amount');
        $type     = $this->request->getPost('type');
        $paidBy   = (int)$this->request->getPost('paid_by');

        // 1. Verifikasi akses trip
        $membership = $this->checkTripAccess($tripId);
        if (!$membership) {
            return redirect()->back()->withInput()->with('error', 'Anda tidak memiliki akses ke trip ini.');
        }

        // 1b. Validasi: jika period_id dipilih, pastikan periode masih open
        if (!empty($periodId)) {
            $chosenPeriod = $this->periodModel->find((int)$periodId);
            if ($chosenPeriod && ($chosenPeriod['status'] ?? 'open') === 'settled') {
                return redirect()->back()->withInput()->with('error', 'Periode "' . esc($chosenPeriod['label']) . '" sudah ditutup (Settled). Transaksi tidak dapat ditambahkan ke periode ini.');
            }
        }

        // 2. Jika tipe individual, lakukan validasi data adjustment
        $adjustments = [];
        if ($type === 'individual') {
            $targets = $this->request->getPost('target_user');
            $amounts = $this->request->getPost('target_amount');
            $notes   = $this->request->getPost('target_note') ?: [];

            if (empty($targets)) {
                return redirect()->back()->withInput()->with('error', 'Untuk transaksi Individual, minimal 1 penerima beban harus diisi.');
            }

            $totalAdjusted = 0;
            foreach ($targets as $index => $targetUserId) {
                $targetAmount = (int)($amounts[$index] ?? 0);
                if ($targetAmount <= 0) continue;

                $totalAdjusted += $targetAmount;
                $adjustments[] = [
                    'target_user_id' => (int)$targetUserId,
                    'amount'         => $targetAmount,
                    'note'           => $notes[$index] ?? ''
                ];
            }

            if ($totalAdjusted !== $amount) {
                return redirect()->back()->withInput()->with('error', "Total nominal pembagian individual (Rp " . number_format($totalAdjusted, 0, ',', '.') . ") harus sama dengan nominal transaksi utama (Rp " . number_format($amount, 0, ',', '.') . ").");
            }
        }

        // 3. Handle upload struk (opsional)
        $receiptPath = null;
        $receiptFile = $this->request->getFile('receipt_image');
        if ($receiptFile && $receiptFile->isValid() && !$receiptFile->hasMoved()) {
            $receiptPath = $this->compressAndSaveImage($receiptFile, 'uploads/receipts');
        }

        // 4. Simpan transaksi utama & detail
        $db = \Config\Database::connect();
        $db->transStart();

        $transId = $this->transactionModel->insert([
            'trip_id'       => $tripId,
            'period_id'     => !empty($periodId) ? (int)$periodId : null,
            'date'          => $this->request->getPost('date'),
            'description'   => $this->request->getPost('description'),
            'amount'        => $amount,
            'paid_by'       => $paidBy,
            'type'          => $type,
            'receipt_image' => $receiptPath,
            'created_by'    => user_id(),
        ]);

        if ($type === 'individual' && !empty($adjustments)) {
            foreach ($adjustments as $adj) {
                $this->adjustmentModel->insert([
                    'transaction_id' => $transId,
                    'target_user_id' => $adj['target_user_id'],
                    'amount'         => $adj['amount'],
                    'note'           => $adj['note'] ?: null
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan transaksi.');
        }

        return redirect()->to('backend/transactions?trip_id=' . $tripId . (!empty($periodId) ? '&period_id=' . $periodId : ''))->with('success', 'Transaksi berhasil dicatat.');
    }

    /**
     * Dapatkan detail transaksi untuk modal edit (JSON)
     */
    public function get(int $transactionId)
    {
        $transaction = $this->transactionModel->find($transactionId);
        if (!$transaction) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Transaksi tidak ditemukan.'])->setStatusCode(404);
        }

        // Verifikasi akses trip
        $membership = $this->checkTripAccess((int)$transaction['trip_id']);
        if (!$membership) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak memiliki akses ke trip ini.'])->setStatusCode(403);
        }

        // Ambil adjustments jika ada
        $adjustments = $this->adjustmentModel->where('transaction_id', $transactionId)->findAll();

        return $this->response->setJSON([
            'status'      => 'ok',
            'transaction' => $transaction,
            'adjustments' => $adjustments,
        ]);
    }

    /**
     * Update transaksi (Shared atau Individual)
     */
    public function update(int $transactionId)
    {
        $transaction = $this->transactionModel->find($transactionId);
        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');
        }

        // Validasi: transaksi di periode yang sudah settled tidak boleh diedit
        if (!empty($transaction['period_id'])) {
            $existingPeriod = $this->periodModel->find((int)$transaction['period_id']);
            if ($existingPeriod && ($existingPeriod['status'] ?? 'open') === 'settled') {
                return redirect()->back()->with('error', 'Transaksi tidak dapat diedit karena periode "' . esc($existingPeriod['label']) . '" sudah ditutup (Settled).');
            }
        }

        $rules = [
            'trip_id'       => 'required|numeric',
            'period_id'     => 'permit_empty',
            'date'          => 'required|valid_date[Y-m-d]',
            'description'   => 'required|min_length[3]|max_length[255]',
            'amount'        => 'required|numeric|greater_than[0]',
            'paid_by'       => 'required|numeric',
            'type'          => 'required|in_list[shared,individual]',
            'receipt_image' => 'permit_empty|is_image[receipt_image]|max_size[receipt_image,5120]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tripId   = (int)$this->request->getPost('trip_id');
        $periodId = $this->request->getPost('period_id');
        $amount   = (int)$this->request->getPost('amount');
        $type     = $this->request->getPost('type');
        $paidBy   = (int)$this->request->getPost('paid_by');

        // 1. Verifikasi akses trip
        $membership = $this->checkTripAccess($tripId);
        if (!$membership) {
            return redirect()->back()->withInput()->with('error', 'Anda tidak memiliki akses ke trip ini.');
        }

        // 1b. Validasi: jika period_id dipilih, pastikan periode masih open
        if (!empty($periodId)) {
            $chosenPeriod = $this->periodModel->find((int)$periodId);
            if ($chosenPeriod && ($chosenPeriod['status'] ?? 'open') === 'settled') {
                return redirect()->back()->withInput()->with('error', 'Periode "' . esc($chosenPeriod['label']) . '" sudah ditutup (Settled). Transaksi tidak dapat diperbarui ke periode ini.');
            }
        }

        // 2. Jika tipe individual, lakukan validasi data adjustment
        $adjustments = [];
        if ($type === 'individual') {
            $targets = $this->request->getPost('target_user');
            $amounts = $this->request->getPost('target_amount');
            $notes   = $this->request->getPost('target_note') ?: [];

            if (empty($targets)) {
                return redirect()->back()->withInput()->with('error', 'Untuk transaksi Individual, minimal 1 penerima beban harus diisi.');
            }

            $totalAdjusted = 0;
            foreach ($targets as $index => $targetUserId) {
                $targetAmount = (int)($amounts[$index] ?? 0);
                if ($targetAmount <= 0) continue;

                $totalAdjusted += $targetAmount;
                $adjustments[] = [
                    'target_user_id' => (int)$targetUserId,
                    'amount'         => $targetAmount,
                    'note'           => $notes[$index] ?? ''
                ];
            }

            if ($totalAdjusted !== $amount) {
                return redirect()->back()->withInput()->with('error', "Total nominal pembagian individual (Rp " . number_format($totalAdjusted, 0, ',', '.') . ") harus sama dengan nominal transaksi utama (Rp " . number_format($amount, 0, ',', '.') . ").");
            }
        }

        // 3. Handle upload struk baru (opsional – pertahankan gambar lama jika tidak ada file baru)
        $receiptPath = $transaction['receipt_image']; // default: tetap gambar lama
        $receiptFile = $this->request->getFile('receipt_image');
        if ($receiptFile && $receiptFile->isValid() && !$receiptFile->hasMoved()) {
            // Hapus file lama jika ada
            if ($receiptPath && file_exists(FCPATH . $receiptPath)) {
                unlink(FCPATH . $receiptPath);
            }
            $receiptPath = $this->compressAndSaveImage($receiptFile, 'uploads/receipts');
        }

        // 4. Simpan transaksi utama & detail
        $db = \Config\Database::connect();
        $db->transStart();

        $this->transactionModel->update($transactionId, [
            'period_id'     => !empty($periodId) ? (int)$periodId : null,
            'date'          => $this->request->getPost('date'),
            'description'   => $this->request->getPost('description'),
            'amount'        => $amount,
            'paid_by'       => $paidBy,
            'type'          => $type,
            'receipt_image' => $receiptPath,
        ]);

        // Hapus adjustments yang lama
        $this->adjustmentModel->where('transaction_id', $transactionId)->delete();

        // Masukkan adjustments baru jika tipe individual
        if ($type === 'individual' && !empty($adjustments)) {
            foreach ($adjustments as $adj) {
                $this->adjustmentModel->insert([
                    'transaction_id' => $transactionId,
                    'target_user_id' => $adj['target_user_id'],
                    'amount'         => $adj['amount'],
                    'note'           => $adj['note'] ?: null
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui transaksi.');
        }

        return redirect()->to('backend/transactions?trip_id=' . $tripId . (!empty($periodId) ? '&period_id=' . $periodId : ''))->with('success', 'Transaksi berhasil diperbarui.');
    }

    /**
     * Hapus transaksi (Hanya Group Admin yang bisa)
     */
    public function delete(int $transactionId)
    {
        $transaction = $this->transactionModel->find($transactionId);
        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');
        }

        // 1. Verifikasi akses trip
        $membership = $this->checkTripAccess((int)$transaction['trip_id']);
        if (!$membership || $membership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat menghapus transaksi.');
        }

        // 2. Validasi: transaksi di periode yang sudah settled tidak boleh dihapus
        if (!empty($transaction['period_id'])) {
            $existingPeriod = $this->periodModel->find((int)$transaction['period_id']);
            if ($existingPeriod && ($existingPeriod['status'] ?? 'open') === 'settled') {
                return redirect()->back()->with('error', 'Transaksi tidak dapat dihapus karena periode "' . esc($existingPeriod['label']) . '" sudah ditutup (Settled).');
            }
        }

        // Hapus file struk dari disk jika ada
        if (!empty($transaction['receipt_image'])) {
            $receiptPath = FCPATH . $transaction['receipt_image'];
            if (file_exists($receiptPath) && is_file($receiptPath)) {
                unlink($receiptPath);
            }
        }

        $this->transactionModel->delete($transactionId);

        return redirect()->to('backend/transactions?trip_id=' . $transaction['trip_id'] . ($transaction['period_id'] ? '&period_id=' . $transaction['period_id'] : ''))->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Ekspor PDF menggunakan Dompdf
     */
    public function pdf()
    {
        $selectedPeriodId = $this->request->getGet('period_id');
        if (empty($selectedPeriodId)) {
            return redirect()->back()->with('error', 'Periode tidak valid.');
        }

        $period = $this->periodModel->find($selectedPeriodId);
        if (!$period) {
            return redirect()->back()->with('error', 'Periode tidak ditemukan.');
        }

        $selectedTripId = $period['trip_id'];
        $selectedTrip = $this->tripModel->find($selectedTripId);
        if (!$selectedTrip) {
            return redirect()->back()->with('error', 'Kegiatan tidak ditemukan.');
        }

        // Verifikasi akses ke trip terpilih
        $currentMembership = $this->checkTripAccess((int)$selectedTripId);
        if (!$currentMembership) {
            return redirect()->to('backend/transactions')->with('error', 'Anda tidak memiliki akses ke kegiatan ini.');
        }

        // Query transaksi
        $transactions = $this->transactionModel->select('transactions.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as paid_by_name, COALESCE(NULLIF(creator.fullname, \'\'), creator.username) as creator_name, trip_periods.label as period_label')
                                             ->join('users', 'users.id = transactions.paid_by')
                                             ->join('users creator', 'creator.id = transactions.created_by')
                                             ->join('trip_periods', 'trip_periods.id = transactions.period_id', 'left')
                                             ->where('transactions.trip_id', $selectedTripId)
                                             ->where('transactions.period_id', $selectedPeriodId)
                                             ->orderBy('paid_by_name', 'ASC')
                                             ->orderBy('transactions.date', 'DESC')
                                             ->findAll();

        // Tambahkan data detail adjustments untuk transaksi bertipe individual
        foreach ($transactions as &$t) {
            if ($t['type'] === 'individual') {
                $t['adjustments'] = $this->adjustmentModel->select('transaction_adjustments.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as username')
                                                          ->join('users', 'users.id = transaction_adjustments.target_user_id')
                                                          ->where('transaction_adjustments.transaction_id', $t['id'])
                                                          ->findAll();
            }
        }

        // Hitung rekap pembagian saldo
        $calculationResult = null;
        $calculationEngine = new \App\Libraries\CalculationEngine();
        try {
            $calculationResult = $calculationEngine->calculatePeriod((int)$selectedPeriodId);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses perhitungan saldo: ' . $e->getMessage());
        }

        // Konfigurasi DOMPDF
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);

        // Render HTML view
        $html = view('backend/transactions/pdf_template', [
            'calculationResult' => $calculationResult,
            'transactions'      => $transactions,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'rekap_saldo_' . str_replace(' ', '_', $calculationResult['period']['label']) . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Compress and resize uploaded image using CodeIgniter 4 Image service
     */
    protected function compressAndSaveImage($file, string $targetFolder): ?string
    {
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        // Ensure target folder exists under FCPATH
        $uploadDir = FCPATH . $targetFolder;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newName = $file->getRandomName();
        $targetPath = $uploadDir . '/' . $newName;

        try {
            // Use CI4 Image Manipulation Service
            \Config\Services::image()
                ->withFile($file->getTempName())
                ->resize(1024, 1024, true, 'auto') // Max height/width 1024px, maintain ratio
                ->save($targetPath, 75); // Quality 75%

            return $targetFolder . '/' . $newName;
        } catch (\Exception $e) {
            // Fallback if image service/GD library fails
            if ($file->move($uploadDir, $newName)) {
                return $targetFolder . '/' . $newName;
            }
            return null;
        }
    }
}
