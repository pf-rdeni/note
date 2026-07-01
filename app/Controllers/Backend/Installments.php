<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\InstallmentModel;
use App\Models\InstallmentPaymentModel;
use App\Models\InstallmentGroupPaymentModel;
use App\Models\TripModel;
use App\Models\GroupMemberModel;
use Myth\Auth\Models\UserModel;

class Installments extends BaseController
{
    protected $installmentModel;
    protected $paymentModel;
    protected $groupPaymentModel;
    protected $tripModel;
    protected $groupMemberModel;
    protected $userModel;

    public function __construct()
    {
        $this->installmentModel  = new InstallmentModel();
        $this->paymentModel      = new InstallmentPaymentModel();
        $this->groupPaymentModel = new InstallmentGroupPaymentModel();
        $this->tripModel         = new TripModel();
        $this->groupMemberModel  = new GroupMemberModel();
        $this->userModel         = new UserModel();
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    protected function checkTripAccess(int $tripId): ?array
    {
        $trip = $this->tripModel->find($tripId);
        if (!$trip) return null;

        return $this->groupMemberModel
            ->where('group_id', $trip['group_id'])
            ->where('user_id', user_id())
            ->first();
    }

    protected function compressAndSaveImage($file, string $targetFolder): ?string
    {
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $uploadDir = FCPATH . $targetFolder;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newName    = $file->getRandomName();
        $targetPath = $uploadDir . '/' . $newName;

        try {
            \Config\Services::image()
                ->withFile($file->getTempName())
                ->resize(1024, 1024, true, 'auto')
                ->save($targetPath, 75);

            return $targetFolder . '/' . $newName;
        } catch (\Exception $e) {
            if ($file->move($uploadDir, $newName)) {
                return $targetFolder . '/' . $newName;
            }
            return null;
        }
    }

    // ----------------------------------------------------------------
    // index() — halaman utama cicilan
    // ----------------------------------------------------------------

    public function index()
    {
        $userId = user_id();

        // Ambil trip yang bisa diakses
        $availableTrips = $this->tripModel
            ->select('trips.*, groups.name as group_name')
            ->join('groups', 'groups.id = trips.group_id')
            ->join('group_members', 'group_members.group_id = groups.id')
            ->where('group_members.user_id', $userId)
            ->findAll();

        $session = session();

        if ($this->request->getGet('reset') !== null) {
            $session->remove('inst_last_trip_id');
            $session->remove('inst_role');
            return redirect()->to('backend/installments');
        }

        $role = $this->request->getGet('role');
        if ($role !== null) {
            $session->set('inst_role', $role);
        } else {
            $role = $session->get('inst_role') ?? 'borrower';
        }

        $selectedTripId = $this->request->getGet('trip_id');
        $fromGet        = ($selectedTripId !== null);

        if (!$fromGet) {
            $selectedTripId = $session->get('inst_last_trip_id');
        }

        if (empty($selectedTripId) && !empty($availableTrips)) {
            $selectedTripId = $availableTrips[0]['id'];
        }

        $selectedTrip      = null;
        $installments      = [];
        $groupPayments     = [];
        $groupMembers      = [];
        $currentMembership = null;
        $monthColumns      = [];
        $groupedData       = [];

        if (!empty($selectedTripId)) {
            $currentMembership = $this->checkTripAccess((int)$selectedTripId);
            if (!$currentMembership) {
                return redirect()->to('backend/installments')->with('error', 'Anda tidak memiliki akses ke trip ini.');
            }

            $selectedTrip = $this->tripModel->find($selectedTripId);

            // Ambil installments yang boleh dilihat user ini
            $installments = $this->installmentModel->getVisibleByUser($userId, (int)$selectedTripId);

            // Filter by role (borrower vs lender)
            $userIdInt = (int)$userId;
            $installments = array_values(array_filter($installments, function($inst) use ($userIdInt, $role) {
                if ($role === 'borrower') {
                    return (int)$inst['borrower_user_id'] === $userIdInt;
                } else {
                    return (int)$inst['lender_user_id'] === $userIdInt;
                }
            }));

            // Ambil jadwal pembayaran per installment
            $installmentIds = array_column($installments, 'id');
            $allPayments    = [];
            if (!empty($installmentIds)) {
                $allPayments = $this->paymentModel
                    ->whereIn('installment_id', $installmentIds)
                    ->orderBy('due_date', 'ASC')
                    ->findAll();
            }

            // Map payments by installment_id + due_date
            $paymentMap = [];
            foreach ($allPayments as $p) {
                $paymentMap[$p['installment_id']][$p['due_date']] = $p;
            }

            // Kumpulkan semua kolom bulan unik
            $monthSet = [];
            foreach ($allPayments as $p) {
                $monthSet[$p['due_date']] = $p['due_date'];
            }
            ksort($monthSet);
            $monthColumns = array_values($monthSet);

            // Ambil riwayat group payments
            $groupPayments = $this->groupPaymentModel->getHistoryByTrip((int)$selectedTripId, $userId);

            // Map group payments by: "lender_id|borrower_id|source_type|due_month"
            $groupPaymentMap = [];
            foreach ($groupPayments as $gp) {
                $key = ($gp['lender_user_id'] ?? 'null') . '|' . $gp['borrower_user_id'] . '|' . $gp['source_type'] . '|' . $gp['due_month'];
                $groupPaymentMap[$key] = $gp;
            }

            // Kelompokkan installments by lender_user_id + source_type
            foreach ($installments as &$inst) {
                $groupKey = $inst['source_type'] . '|' . ($inst['lender_user_id'] ?? 'null') . '|' . $inst['borrower_user_id'];
                $inst['payments'] = $paymentMap[$inst['id']] ?? [];

                if (!isset($groupedData[$groupKey])) {
                    $groupedData[$groupKey] = [
                        'source_type'      => $inst['source_type'],
                        'lender_user_id'   => $inst['lender_user_id'],
                        'lender_name'      => $inst['lender_name'] ?? null,
                        'borrower_user_id' => $inst['borrower_user_id'],
                        'borrower_name'    => $inst['borrower_name'],
                        'installments'     => [],
                    ];
                }
                $groupedData[$groupKey]['installments'][] = $inst;
            }

            // Ambil anggota grup untuk dropdown form
            $groupMembers = $this->groupMemberModel
                ->select('group_members.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as username')
                ->join('users', 'users.id = group_members.user_id')
                ->where('group_members.group_id', $selectedTrip['group_id'])
                ->findAll();

            $session->set('inst_last_trip_id', $selectedTripId);
        }

        // Ambil data untuk Ringkasan Tagihan Bulanan (dari semua trip yang bisa diakses)
        $allTripIds = array_column($availableTrips, 'id');
        $allInstallmentsForSummary = [];
        $summaryMonthColumns = [];
        $allGroupPaymentMap = [];

        if (!empty($allTripIds)) {
            $allInstallmentsForSummary = $this->installmentModel->getVisibleByUserAllTrips($userId, $allTripIds);

            // Filter by role (borrower vs lender)
            $userIdInt = (int)$userId;
            $allInstallmentsForSummary = array_values(array_filter($allInstallmentsForSummary, function($inst) use ($userIdInt, $role) {
                if ($role === 'borrower') {
                    return (int)$inst['borrower_user_id'] === $userIdInt;
                } else {
                    return (int)$inst['lender_user_id'] === $userIdInt;
                }
            }));
            $allInstallmentIds = array_column($allInstallmentsForSummary, 'id');
            $allPaymentsForSummary = [];
            if (!empty($allInstallmentIds)) {
                $allPaymentsForSummary = $this->paymentModel
                    ->whereIn('installment_id', $allInstallmentIds)
                    ->orderBy('due_date', 'ASC')
                    ->findAll();
            }

            // Map payments by installment_id + due_date
            $summaryPaymentMap = [];
            foreach ($allPaymentsForSummary as $p) {
                $summaryPaymentMap[$p['installment_id']][$p['due_date']] = $p;
            }

            // Kumpulkan bulan unik untuk summary
            $summaryMonthSet = [];
            foreach ($allPaymentsForSummary as $p) {
                $summaryMonthSet[$p['due_date']] = $p['due_date'];
            }
            ksort($summaryMonthSet);
            $summaryMonthColumns = array_values($summaryMonthSet);

            // Tempelkan payments ke installments
            foreach ($allInstallmentsForSummary as &$inst) {
                $inst['payments'] = $summaryPaymentMap[$inst['id']] ?? [];
            }

            // Ambil seluruh riwayat group payments
            $allGroupPayments = $this->groupPaymentModel->getHistoryAllTrips($allTripIds, $userId);
            foreach ($allGroupPayments as $gp) {
                $gpKey = $gp['trip_id'] . '|' . ($gp['lender_user_id'] ?? 'null') . '|' . $gp['borrower_user_id'] . '|' . $gp['source_type'] . '|' . date('Y-m-01', strtotime($gp['due_month']));
                $allGroupPaymentMap[$gpKey] = $gp;
            }
        }

        return view('backend/installments/index', [
            'pageTitle'                 => 'Cicilan',
            'role'                      => $role,
            'availableTrips'            => $availableTrips,
            'selectedTripId'            => $selectedTripId,
            'selectedTrip'              => $selectedTrip,
            'groupedData'               => $groupedData,
            'monthColumns'              => $monthColumns,
            'groupMembers'              => $groupMembers,
            'currentMembership'         => $currentMembership,
            'user'                      => user(),
            'allInstallmentsForSummary' => $allInstallmentsForSummary,
            'summaryMonthColumns'       => $summaryMonthColumns,
            'allGroupPaymentMap'        => $allGroupPaymentMap,
        ]);
    }

    // ----------------------------------------------------------------
    // simulate() — AJAX kalkulasi preview tabel cicilan
    // ----------------------------------------------------------------

    public function simulate()
    {
        $startDate = $this->request->getGet('start_date');    // YYYY-MM-01
        $months    = (int)$this->request->getGet('months');
        $total     = (int)$this->request->getGet('total');
        $monthly   = (int)$this->request->getGet('monthly');

        if ($months <= 0) {
            return $this->response->setJSON(['error' => 'Durasi tidak valid.'])->setStatusCode(422);
        }

        // Hitung dari total atau dari per-bulan
        if ($total > 0 && $monthly === 0) {
            $monthly = (int)round($total / $months);
        } elseif ($monthly > 0 && $total === 0) {
            $total = $monthly * $months;
        }

        if ($monthly <= 0) {
            return $this->response->setJSON(['error' => 'Nominal cicilan tidak valid.'])->setStatusCode(422);
        }

        $schedule = [];
        $date     = new \DateTime($startDate);
        $date->modify('first day of this month');

        for ($i = 0; $i < $months; $i++) {
            $schedule[] = [
                'due_date'   => $date->format('Y-m-d'),
                'due_label'  => $date->format('M\'y'),
                'due_amount' => $monthly,
            ];
            $date->modify('+1 month');
        }

        return $this->response->setJSON([
            'total'    => $total,
            'monthly'  => $monthly,
            'months'   => $months,
            'schedule' => $schedule,
        ]);
    }

    // ----------------------------------------------------------------
    // get(int $id) — AJAX: detail cicilan untuk modal edit
    // ----------------------------------------------------------------

    public function get(int $id)
    {
        $installment = $this->installmentModel->find($id);
        if (!$installment) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cicilan tidak ditemukan.'])->setStatusCode(404);
        }

        // Cek akses: hanya borrower atau lender
        $uid = user_id();
        if ((int)$installment['borrower_user_id'] !== $uid && (int)$installment['lender_user_id'] !== $uid) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.'])->setStatusCode(403);
        }

        $payments = $this->paymentModel->getByInstallment($id);

        return $this->response->setJSON([
            'status'      => 'ok',
            'installment' => $installment,
            'payments'    => $payments,
        ]);
    }

    // ----------------------------------------------------------------
    // store() — simpan cicilan baru
    // ----------------------------------------------------------------

    public function store()
    {
        $rules = [
            'trip_id'            => 'required|numeric',
            'description'        => 'required|min_length[3]|max_length[255]',
            'source_type'        => 'required|in_list[member_loan,credit_card]',
            'lender_user_id'     => 'permit_empty|numeric',
            'calc_mode'          => 'required|in_list[total_months,monthly_duration]',
            'total_amount'       => 'permit_empty|numeric',
            'monthly_amount'     => 'permit_empty|numeric',
            'installment_months' => 'required|numeric|greater_than[0]',
            'start_date'         => 'required|valid_date[Y-m]',
            'note'               => 'permit_empty|max_length[500]',
            'split_type'         => 'permit_empty|in_list[equal,individual]',
            'borrowers'          => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tripId      = (int)$this->request->getPost('trip_id');
        $sourceType  = $this->request->getPost('source_type');
        $calcMode    = $this->request->getPost('calc_mode');
        $months      = (int)$this->request->getPost('installment_months');
        $startDate   = $this->request->getPost('start_date');
        $lenderId    = $sourceType === 'member_loan' ? (int)$this->request->getPost('lender_user_id') : null;
        $borrowers   = $this->request->getPost('borrowers');
        $splitType   = $this->request->getPost('split_type') ?: 'equal';

        // Validasi akses trip
        $membership = $this->checkTripAccess($tripId);
        if (!$membership) {
            return redirect()->back()->withInput()->with('error', 'Anda tidak memiliki akses ke trip ini.');
        }

        // Hitung nominal berdasarkan mode kalkulasi
        $totalAmount   = 0;
        $monthlyAmount = 0;

        if ($calcMode === 'total_months') {
            $totalAmount   = (int)$this->request->getPost('total_amount');
            $monthlyAmount = (int)round($totalAmount / $months);
        } else {
            $monthlyAmount = (int)$this->request->getPost('monthly_amount');
            $totalAmount   = $monthlyAmount * $months;
        }

        if ($totalAmount <= 0 || $monthlyAmount <= 0) {
            return redirect()->back()->withInput()->with('error', 'Nominal cicilan tidak valid.');
        }

        // Validasi khusus: member_loan harus ada lender dan minimal satu borrower
        if ($sourceType === 'member_loan') {
            if (empty($lenderId)) {
                return redirect()->back()->withInput()->with('error', 'Pilih anggota pemberi pinjaman.');
            }
            if (empty($borrowers) || !is_array($borrowers)) {
                return redirect()->back()->withInput()->with('error', 'Pilih minimal satu peminjam.');
            }
        }

        // Simpan ke DB dalam transaksi
        $db = \Config\Database::connect();
        $db->transStart();

        if ($sourceType === 'member_loan') {
            $divisor = count($borrowers);
            if ($divisor <= 0) $divisor = 1;

            $createdCount = 0;
            foreach ($borrowers as $borrowerId) {
                $borrowerId = (int)$borrowerId;

                $sourceTypeForThis = $sourceType;
                $lenderIdForThis   = $lenderId;

                // Jika peminjam sama dengan pemberi pinjaman, konversi menjadi "Pinjaman Pribadi" (tidak memiliki lender di grup)
                if ($borrowerId === $lenderId) {
                    $sourceTypeForThis = 'credit_card';
                    $lenderIdForThis   = null;
                }

                $individualTotal   = $totalAmount;
                $individualMonthly = $monthlyAmount;

                if ($splitType === 'equal') {
                    $individualTotal   = (int)round($totalAmount / $divisor);
                    $individualMonthly = (int)round($monthlyAmount / $divisor);
                }

                $installmentId = $this->installmentModel->insert([
                    'trip_id'            => $tripId,
                    'description'        => $this->request->getPost('description'),
                    'source_type'        => $sourceTypeForThis,
                    'lender_user_id'     => $lenderIdForThis,
                    'borrower_user_id'   => $borrowerId,
                    'total_amount'       => $individualTotal,
                    'start_date'         => date('Y-m-01', strtotime($startDate)),
                    'installment_months' => $months,
                    'monthly_amount'     => $individualMonthly,
                    'note'               => $this->request->getPost('note') ?: null,
                    'status'             => 'active',
                    'created_by'         => user_id(),
                ]);

                // Generate jadwal pembayaran otomatis
                $this->paymentModel->generateSchedule(
                    $installmentId,
                    date('Y-m-01', strtotime($startDate)),
                    $months,
                    $individualMonthly
                );

                $createdCount++;
            }

            if ($createdCount === 0) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Semua peminjam yang dipilih dilewati karena peminjam sama dengan pemberi pinjaman.');
            }
        } else {
            // Untuk Kartu Kredit Pribadi, borrower adalah diri sendiri (creator)
            $installmentId = $this->installmentModel->insert([
                'trip_id'            => $tripId,
                'description'        => $this->request->getPost('description'),
                'source_type'        => $sourceType,
                'lender_user_id'     => null,
                'borrower_user_id'   => user_id(),
                'total_amount'       => $totalAmount,
                'start_date'         => date('Y-m-01', strtotime($startDate)),
                'installment_months' => $months,
                'monthly_amount'     => $monthlyAmount,
                'note'               => $this->request->getPost('note') ?: null,
                'status'             => 'active',
                'created_by'         => user_id(),
            ]);

            // Generate jadwal pembayaran otomatis
            $this->paymentModel->generateSchedule(
                $installmentId,
                date('Y-m-01', strtotime($startDate)),
                $months,
                $monthlyAmount
            );
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan cicilan.');
        }

        return redirect()->to('backend/installments?trip_id=' . $tripId)->with('success', 'Cicilan "' . esc($this->request->getPost('description')) . '" berhasil ditambahkan.');
    }

    // ----------------------------------------------------------------
    // update(int $id) — edit cicilan (hanya jika belum ada yang dibayar)
    // ----------------------------------------------------------------

    public function update(int $id)
    {
        $installment = $this->installmentModel->find($id);
        if (!$installment) {
            return redirect()->back()->with('error', 'Cicilan tidak ditemukan.');
        }

        // Hanya borrower yang bisa edit
        if ((int)$installment['borrower_user_id'] !== (int)user_id()) {
            return redirect()->back()->with('error', 'Hanya peminjam yang dapat mengedit cicilan ini.');
        }

        // Cek apakah sudah ada yang dibayar
        $paidCount = $this->paymentModel
            ->where('installment_id', $id)
            ->where('status', 'paid')
            ->countAllResults();

        $rules = [
            'description' => 'required|min_length[3]|max_length[255]',
            'note'        => 'permit_empty|max_length[500]',
        ];

        if ($paidCount === 0) {
            // Edit seluruh field
            $rules['source_type']        = 'required|in_list[member_loan,credit_card]';
            $rules['lender_user_id']     = 'permit_empty|numeric';
            $rules['calc_mode']          = 'required|in_list[total_months,monthly_duration]';
            $rules['total_amount']       = 'permit_empty|numeric';
            $rules['monthly_amount']     = 'permit_empty|numeric';
            $rules['installment_months'] = 'required|numeric|greater_than[0]';
            $rules['start_date']         = 'required|valid_date[Y-m]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        if ($paidCount > 0) {
            // Hanya update deskripsi dan catatan
            $this->installmentModel->update($id, [
                'description' => $this->request->getPost('description'),
                'note'        => $this->request->getPost('note') ?: null,
            ]);
        } else {
            // Update seluruh field & regenerasi jadwal
            $sourceType  = $this->request->getPost('source_type');
            $calcMode    = $this->request->getPost('calc_mode');
            $months      = (int)$this->request->getPost('installment_months');
            $startDate   = $this->request->getPost('start_date');
            $lenderId    = $sourceType === 'member_loan' ? (int)$this->request->getPost('lender_user_id') : null;

            $totalAmount   = 0;
            $monthlyAmount = 0;

            if ($calcMode === 'total_months') {
                $totalAmount   = (int)$this->request->getPost('total_amount');
                $monthlyAmount = (int)round($totalAmount / $months);
            } else {
                $monthlyAmount = (int)$this->request->getPost('monthly_amount');
                $totalAmount   = $monthlyAmount * $months;
            }

            if ($totalAmount <= 0 || $monthlyAmount <= 0) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Nominal cicilan tidak valid.');
            }

            $this->installmentModel->update($id, [
                'description'        => $this->request->getPost('description'),
                'source_type'        => $sourceType,
                'lender_user_id'     => $lenderId,
                'total_amount'       => $totalAmount,
                'start_date'         => date('Y-m-01', strtotime($startDate)),
                'installment_months' => $months,
                'monthly_amount'     => $monthlyAmount,
                'note'               => $this->request->getPost('note') ?: null,
            ]);

            // Hapus jadwal lama & generate jadwal baru
            $this->paymentModel->where('installment_id', $id)->delete();
            $this->paymentModel->generateSchedule(
                $id,
                date('Y-m-01', strtotime($startDate)),
                $months,
                $monthlyAmount
            );
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui cicilan.');
        }

        return redirect()->to('backend/installments?trip_id=' . $installment['trip_id'])->with('success', 'Cicilan berhasil diperbarui.');
    }

    // ----------------------------------------------------------------
    // delete(int $id) — hapus cicilan
    // ----------------------------------------------------------------

    public function delete(int $id)
    {
        $installment = $this->installmentModel->find($id);
        if (!$installment) {
            return redirect()->back()->with('error', 'Cicilan tidak ditemukan.');
        }

        $membership = $this->checkTripAccess((int)$installment['trip_id']);
        if (!$membership) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Hanya borrower atau admin trip yang bisa hapus
        $isBorrower = ((int)$installment['borrower_user_id'] === (int)user_id());
        if (!$isBorrower && $membership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya peminjam atau admin yang dapat menghapus cicilan ini.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Get all payments for this installment
        $payments = $this->paymentModel->where('installment_id', $id)->findAll();

        foreach ($payments as $payment) {
            if ($payment['status'] === 'paid') {
                // Find matching group payment
                $dueMonth = date('Y-m-01', strtotime($payment['due_date']));
                $gpQuery = $this->groupPaymentModel
                    ->where('trip_id', $installment['trip_id'])
                    ->where('borrower_user_id', $installment['borrower_user_id'])
                    ->where('source_type', $installment['source_type'])
                    ->where('due_month', $dueMonth);
                
                if ($installment['source_type'] === 'member_loan') {
                    $gpQuery->where('lender_user_id', $installment['lender_user_id']);
                } else {
                    $gpQuery->where('lender_user_id IS NULL');
                }
                
                $gp = $gpQuery->first();

                if ($gp) {
                    // Check if there are other paid payments in the same group payment
                    $otherPaidQuery = $this->paymentModel
                        ->join('installments', 'installments.id = installment_payments.installment_id')
                        ->where('installment_payments.installment_id !=', $id)
                        ->where('installments.trip_id', $installment['trip_id'])
                        ->where('installments.borrower_user_id', $installment['borrower_user_id'])
                        ->where('installments.source_type', $installment['source_type'])
                        ->where('installment_payments.due_date', $payment['due_date'])
                        ->where('installment_payments.status', 'paid');
                    
                    if ($installment['source_type'] === 'member_loan') {
                        $otherPaidQuery->where('installments.lender_user_id', $installment['lender_user_id']);
                    } else {
                        $otherPaidQuery->where('installments.lender_user_id IS NULL');
                    }

                    $otherPaidCount = $otherPaidQuery->countAllResults();

                    if ($otherPaidCount === 0) {
                        // Delete group payment & delete image file
                        if (!empty($gp['proof_image'])) {
                            $filePath = FCPATH . $gp['proof_image'];
                            if (file_exists($filePath) && is_file($filePath)) {
                                @unlink($filePath);
                            }
                        }
                        $this->groupPaymentModel->delete($gp['id']);
                    } else {
                        // Subtract amount from group payment totals
                        $newTotal = max(0, (int)$gp['total_due'] - (int)$payment['due_amount']);
                        $this->groupPaymentModel->update($gp['id'], [
                            'total_due' => $newTotal,
                            'total_paid' => $newTotal
                        ]);
                    }
                }
            }
        }

        // Delete payment records first
        $this->paymentModel->where('installment_id', $id)->delete();
        // Delete installment
        $this->installmentModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal menghapus cicilan.');
        }

        return redirect()->to('backend/installments?trip_id=' . $installment['trip_id'])->with('success', 'Cicilan dan seluruh data/bukti pembayaran terkait berhasil dihapus.');
    }

    // ----------------------------------------------------------------
    // payGroup() — bayar cicilan ke lender (member_loan) — WAJIB upload
    // ----------------------------------------------------------------

    public function payGroup()
    {
        $rules = [
            'trip_id'          => 'required|numeric',
            'lender_user_id'   => 'required|numeric',
            'borrower_user_id' => 'required|numeric',
            'due_month'        => 'required|valid_date[Y-m-d]',
            'paid_at'          => 'required|valid_date[Y-m-d]',
            'proof_image'      => 'uploaded[proof_image]|is_image[proof_image]|max_size[proof_image,5120]',
            'note'             => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tripId      = (int)$this->request->getPost('trip_id');
        $lenderId    = (int)$this->request->getPost('lender_user_id');
        $borrowerId  = (int)$this->request->getPost('borrower_user_id');
        $dueMonth    = $this->request->getPost('due_month'); // YYYY-MM-01
        $paidAt      = $this->request->getPost('paid_at');

        // Hanya borrower sendiri yang bisa bayar
        if ($borrowerId !== (int)user_id()) {
            return redirect()->back()->with('error', 'Anda hanya dapat membayar cicilan atas nama Anda sendiri.');
        }

        $membership = $this->checkTripAccess($tripId);
        if (!$membership) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Cek sudah dibayar sebelumnya?
        if ($this->groupPaymentModel->isPaid($borrowerId, $lenderId, $dueMonth, 'member_loan')) {
            return redirect()->back()->with('error', 'Pembayaran bulan ini ke lender ini sudah tercatat sebelumnya.');
        }

        // Ambil semua installment aktif untuk lender-borrower pair ini
        $installments = $this->installmentModel
            ->where('trip_id', $tripId)
            ->where('lender_user_id', $lenderId)
            ->where('borrower_user_id', $borrowerId)
            ->where('source_type', 'member_loan')
            ->where('status', 'active')
            ->findAll();

        if (empty($installments)) {
            return redirect()->back()->with('error', 'Tidak ada cicilan aktif yang ditemukan.');
        }

        $installmentIds = array_column($installments, 'id');

        // Ambil jadwal yang jatuh tempo bulan ini
        $duePayments = $this->paymentModel->getByInstallmentIdsAndMonth($installmentIds, $dueMonth);

        if (empty($duePayments)) {
            return redirect()->back()->with('error', 'Tidak ada tagihan yang jatuh tempo pada bulan ini.');
        }

        $totalDue = array_sum(array_column($duePayments, 'due_amount'));

        // Upload bukti transfer
        $img = $this->request->getFile('proof_image');
        $proofPath = $this->compressAndSaveImage($img, 'uploads/installments');
        if (!$proofPath) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan bukti transfer. Pastikan format file gambar valid.');
        }

        // Simpan dalam transaksi
        $db = \Config\Database::connect();
        $db->transStart();

        // Insert group payment record
        $this->groupPaymentModel->insert([
            'trip_id'          => $tripId,
            'lender_user_id'   => $lenderId,
            'borrower_user_id' => $borrowerId,
            'source_type'      => 'member_loan',
            'due_month'        => $dueMonth,
            'total_due'        => $totalDue,
            'total_paid'       => $totalDue,
            'status'           => 'paid',
            'paid_at'          => $paidAt,
            'proof_image'      => $proofPath,
            'note'             => $this->request->getPost('note') ?: null,
            'created_by'       => user_id(),
        ]);

        // Update setiap installment_payment menjadi paid
        foreach ($duePayments as $dp) {
            $this->paymentModel->update($dp['id'], [
                'status'      => 'paid',
                'paid_amount' => $dp['due_amount'],
                'paid_at'     => $paidAt,
            ]);
        }

        // Cek apakah setiap installment sudah selesai semua bulannya
        foreach ($installmentIds as $instId) {
            if ($this->paymentModel->allPaid($instId)) {
                $this->installmentModel->update($instId, ['status' => 'completed']);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal menyimpan data pembayaran.');
        }

        return redirect()->to('backend/installments?trip_id=' . $tripId)->with('success', 'Pembayaran cicilan bulan ' . date('M Y', strtotime($dueMonth)) . ' berhasil dicatat.');
    }

    // ----------------------------------------------------------------
    // markSelfPaid() — bayar tagihan CC pribadi — WAJIB upload bukti
    // ----------------------------------------------------------------

    public function markSelfPaid()
    {
        $rules = [
            'trip_id'          => 'required|numeric',
            'borrower_user_id' => 'required|numeric',
            'due_month'        => 'required|valid_date[Y-m-d]',
            'paid_at'          => 'required|valid_date[Y-m-d]',
            'proof_image'      => 'uploaded[proof_image]|is_image[proof_image]|max_size[proof_image,5120]',
            'note'             => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tripId     = (int)$this->request->getPost('trip_id');
        $borrowerId = (int)$this->request->getPost('borrower_user_id');
        $dueMonth   = $this->request->getPost('due_month');
        $paidAt     = $this->request->getPost('paid_at');

        // Hanya diri sendiri
        if ($borrowerId !== (int)user_id()) {
            return redirect()->back()->with('error', 'Anda hanya dapat menandai cicilan atas nama Anda sendiri.');
        }

        $membership = $this->checkTripAccess($tripId);
        if (!$membership) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Cek sudah dibayar sebelumnya?
        if ($this->groupPaymentModel->isPaid($borrowerId, null, $dueMonth, 'credit_card')) {
            return redirect()->back()->with('error', 'Pembayaran tagihan CC bulan ini sudah tercatat sebelumnya.');
        }

        // Ambil installments CC aktif
        $installments = $this->installmentModel
            ->where('trip_id', $tripId)
            ->where('borrower_user_id', $borrowerId)
            ->where('source_type', 'credit_card')
            ->where('status', 'active')
            ->findAll();

        if (empty($installments)) {
            return redirect()->back()->with('error', 'Tidak ada cicilan kartu kredit aktif yang ditemukan.');
        }

        $installmentIds = array_column($installments, 'id');
        $duePayments    = $this->paymentModel->getByInstallmentIdsAndMonth($installmentIds, $dueMonth);

        if (empty($duePayments)) {
            return redirect()->back()->with('error', 'Tidak ada tagihan CC yang jatuh tempo pada bulan ini.');
        }

        $totalDue = array_sum(array_column($duePayments, 'due_amount'));

        // Upload bukti tagihan CC
        $img = $this->request->getFile('proof_image');
        $proofPath = $this->compressAndSaveImage($img, 'uploads/installments');
        if (!$proofPath) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan bukti. Pastikan format file gambar valid.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->groupPaymentModel->insert([
            'trip_id'          => $tripId,
            'lender_user_id'   => null,
            'borrower_user_id' => $borrowerId,
            'source_type'      => 'credit_card',
            'due_month'        => $dueMonth,
            'total_due'        => $totalDue,
            'total_paid'       => $totalDue,
            'status'           => 'paid',
            'paid_at'          => $paidAt,
            'proof_image'      => $proofPath,
            'note'             => $this->request->getPost('note') ?: null,
            'created_by'       => user_id(),
        ]);

        foreach ($duePayments as $dp) {
            $this->paymentModel->update($dp['id'], [
                'status'      => 'paid',
                'paid_amount' => $dp['due_amount'],
                'paid_at'     => $paidAt,
            ]);
        }

        foreach ($installmentIds as $instId) {
            if ($this->paymentModel->allPaid($instId)) {
                $this->installmentModel->update($instId, ['status' => 'completed']);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal menyimpan data.');
        }

        return redirect()->to('backend/installments?trip_id=' . $tripId)->with('success', 'Tagihan CC bulan ' . date('M Y', strtotime($dueMonth)) . ' berhasil dicatat sebagai lunas.');
    }
}
