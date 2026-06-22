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

        $selectedTripId = $this->request->getGet('trip_id');
        if (empty($selectedTripId) && !empty($availableTrips)) {
            $selectedTripId = $availableTrips[0]['id'];
        }

        $transactions = [];
        $selectedTrip = null;
        $periods = [];
        $groupMembers = [];
        $selectedPeriodId = $this->request->getGet('period_id');
        $currentMembership = null;

        if (!empty($selectedTripId)) {
            // 2. Verifikasi akses ke trip terpilih
            $currentMembership = $this->checkTripAccess((int)$selectedTripId);
            if (!$currentMembership) {
                return redirect()->to('backend/transactions')->with('error', 'Anda tidak memiliki akses ke trip ini.');
            }

            $selectedTrip = $this->tripModel->find($selectedTripId);
            
            // 3. Dapatkan list periode untuk filter
            $periods = $this->periodModel->where('trip_id', $selectedTripId)->orderBy('created_at', 'ASC')->findAll();

            // 4. Query transaksi
            $transQuery = $this->transactionModel->select('transactions.*, users.username as paid_by_name, creator.username as creator_name, trip_periods.label as period_label')
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
                    $t['adjustments'] = $this->adjustmentModel->select('transaction_adjustments.*, users.username')
                                                              ->join('users', 'users.id = transaction_adjustments.target_user_id')
                                                              ->where('transaction_adjustments.transaction_id', $t['id'])
                                                              ->findAll();
                }
            }

            // 5. Ambil semua anggota grup untuk input form modal
            $groupMembers = $this->groupMemberModel->select('group_members.*, users.username')
                                                   ->join('users', 'users.id = group_members.user_id')
                                                   ->where('group_members.group_id', $selectedTrip['group_id'])
                                                   ->findAll();
        }

        $calculationResult = null;
        if (!empty($selectedPeriodId)) {
            $calculationEngine = new \App\Libraries\CalculationEngine();
            try {
                $calculationResult = $calculationEngine->calculatePeriod((int)$selectedPeriodId);
            } catch (\Exception $e) {
                // Biarkan null jika gagal
            }
        }

        $data = [
            'pageTitle'         => 'Catatan Transaksi',
            'availableTrips'    => $availableTrips,
            'selectedTripId'    => $selectedTripId,
            'selectedTrip'      => $selectedTrip,
            'selectedPeriodId'  => $selectedPeriodId,
            'periods'           => $periods,
            'transactions'      => $transactions,
            'groupMembers'      => $groupMembers,
            'currentMembership' => $currentMembership,
            'calculationResult' => $calculationResult,
            'user'              => user(),
        ];

        return view('backend/transactions/index', $data);
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

        $this->transactionModel->delete($transactionId);

        return redirect()->to('backend/transactions?trip_id=' . $transaction['trip_id'] . ($transaction['period_id'] ? '&period_id=' . $transaction['period_id'] : ''))->with('success', 'Transaksi berhasil dihapus.');
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
