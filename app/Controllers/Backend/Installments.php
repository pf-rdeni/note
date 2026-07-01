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

    public function downloadPdf($monthCol)
    {
        $userId = user_id();
        $role = $this->request->getGet('role') ?? 'borrower';
        $tripId = $this->request->getGet('trip_id');

        if (empty($tripId)) {
            return $this->response->setBody("Error: Trip ID tidak boleh kosong.")->setStatusCode(400);
        }

        // Check trip access
        $currentMembership = $this->checkTripAccess((int)$tripId);
        if (!$currentMembership) {
            return $this->response->setBody("Error: Anda tidak memiliki akses ke trip ini.")->setStatusCode(403);
        }

        $selectedTrip = $this->tripModel->find($tripId);
        if (!$selectedTrip) {
            return $this->response->setBody("Error: Trip tidak ditemukan.")->setStatusCode(404);
        }

        // Get installments visible to user in this trip
        $installments = $this->installmentModel->getVisibleByUser($userId, (int)$tripId);
        $userIdInt = (int)$userId;

        // Filter by role
        $installments = array_values(array_filter($installments, function($inst) use ($userIdInt, $role) {
            if ($role === 'borrower') {
                return (int)$inst['borrower_user_id'] === $userIdInt;
            } else {
                return (int)$inst['lender_user_id'] === $userIdInt;
            }
        }));

        if (empty($installments)) {
            return $this->response->setBody("Error: Tidak ada data cicilan ditemukan.")->setStatusCode(404);
        }

        // Get installment payments for this specific monthCol and installment IDs
        $installmentIds = array_column($installments, 'id');
        $payments = $this->paymentModel
            ->whereIn('installment_id', $installmentIds)
            ->where('due_date', $monthCol)
            ->findAll();

        if (empty($payments)) {
            return $this->response->setBody("Error: Tidak ada jadwal tagihan untuk bulan " . date('M Y', strtotime($monthCol)) . ".")->setStatusCode(404);
        }

        $installmentMap = [];
        foreach ($installments as $inst) {
            $installmentMap[$inst['id']] = $inst;
        }

        $items = [];
        $totalAmount = 0;
        $statusLunas = true;
        $proofImages = [];

        foreach ($payments as $p) {
            $inst = $installmentMap[$p['installment_id']] ?? null;
            if (!$inst) continue;

            if ($p['status'] !== 'paid') {
                $statusLunas = false;
            }

            $dueAmt = (int)$p['due_amount'];
            $totalAmount += $dueAmt;

            $isLoan = ($inst['source_type'] === 'member_loan');
            $lenderName = $isLoan ? ($inst['lender_name'] ?? 'Anggota') : 'Pinjaman Pribadi';
            $borrowerName = $inst['borrower_name'] ?? 'Anggota';

            $items[] = [
                'description'  => $inst['description'],
                'source_type'  => $inst['source_type'],
                'lender_name'  => $lenderName,
                'borrower_name'=> $borrowerName,
                'amount'       => $dueAmt,
                'status'       => $p['status']
            ];

            // Try to find group payment record for proof image
            $gpRecord = $this->groupPaymentModel
                ->where([
                    'trip_id'          => $inst['trip_id'],
                    'lender_user_id'   => $inst['lender_user_id'],
                    'borrower_user_id' => $inst['borrower_user_id'],
                    'source_type'      => $inst['source_type'],
                    'due_month'        => date('Y-m-01', strtotime($monthCol))
                ])->first();

            if ($gpRecord && !empty($gpRecord['proof_image'])) {
                $imgPath = FCPATH . $gpRecord['proof_image'];
                if (file_exists($imgPath)) {
                    $type = pathinfo($imgPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($imgPath);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    
                    $key = ($inst['lender_user_id'] ?? 'null') . '|' . $inst['borrower_user_id'] . '|' . $inst['source_type'];
                    if (!isset($proofImages[$key])) {
                        $proofImages[$key] = [
                            'base64' => $base64,
                            'from'   => $borrowerName,
                            'to'     => $lenderName,
                            'amount' => $gpRecord['total_paid']
                        ];
                    }
                }
            }
        }

        // Render HTML for Dompdf
        $userObj = user();
        $userFullname = !empty($userObj->fullname) ? $userObj->fullname : $userObj->username;

        $monthLabel = date('M Y', strtotime($monthCol));
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Laporan Tagihan Cicilan - ' . $monthLabel . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    color: #333;
                    line-height: 1.4;
                    font-size: 11pt;
                }
                .header-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                .header-logo {
                    font-size: 20pt;
                    font-weight: bold;
                    color: #007bff;
                }
                .header-title {
                    text-align: right;
                    font-size: 14pt;
                    font-weight: bold;
                    color: #555;
                }
                .info-section {
                    margin-bottom: 25px;
                }
                .info-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .info-table td {
                    padding: 4px 0;
                    vertical-align: top;
                }
                .label {
                    color: #777;
                    width: 130px;
                    font-weight: bold;
                }
                .status-badge {
                    display: inline-block;
                    padding: 4px 10px;
                    font-weight: bold;
                    border-radius: 4px;
                    color: #fff;
                    font-size: 10pt;
                }
                .status-lunas {
                    background-color: #28a745;
                }
                .status-tertagih {
                    background-color: #dc3545;
                }
                .items-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 30px;
                }
                .items-table th {
                    background-color: #f1f3f5;
                    border-bottom: 2px solid #dee2e6;
                    padding: 8px;
                    font-weight: bold;
                    text-align: left;
                    font-size: 10pt;
                }
                .items-table td {
                    padding: 8px;
                    border-bottom: 1px solid #dee2e6;
                    font-size: 10pt;
                }
                .text-right {
                    text-align: right;
                }
                .total-row {
                    font-weight: bold;
                    background-color: #f8f9fa;
                }
                .proof-section {
                    page-break-inside: avoid;
                    margin-top: 20px;
                    border-top: 2px dashed #ccc;
                    padding-top: 20px;
                }
                .proof-title {
                    font-size: 12pt;
                    font-weight: bold;
                    margin-bottom: 15px;
                    color: #333;
                }
                .proof-container {
                    margin-bottom: 20px;
                    display: inline-block;
                    margin-right: 20px;
                    vertical-align: top;
                }
                .proof-img {
                    max-width: 250px;
                    max-height: 250px;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 4px;
                    background-color: #fff;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 8pt;
                    color: #777;
                    text-align: center;
                    border-top: 1px solid #eee;
                    padding-top: 8px;
                }
            </style>
        </head>
        <body>
            <table class="header-table">
                <tr>
                    <td class="header-logo">Split Bill Keluarga</td>
                    <td class="header-title">LAPORAN TAGIHAN CICILAN</td>
                </tr>
            </table>

            <div class="info-section">
                <table class="info-table">
                    <tr>
                        <td class="label">Bulan Tagihan:</td>
                        <td>' . date('F Y', strtotime($monthCol)) . '</td>
                        <td class="label" style="text-align: right;">Status Pembayaran:</td>
                        <td style="text-align: right; width: 150px;">
                            ' . ($statusLunas 
                                ? '<span class="status-badge status-lunas">LUNAS</span>' 
                                : '<span class="status-badge status-tertagih">BELUM LUNAS</span>') . '
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Nama Kegiatan:</td>
                        <td>' . esc($selectedTrip['name']) . '</td>
                        <td class="label" style="text-align: right;">Diunduh Oleh:</td>
                        <td style="text-align: right;">' . esc($userFullname) . ' (' . ($role === 'borrower' ? 'Peminjam' : 'Pemberi') . ')</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Cetak:</td>
                        <td>' . date('d F Y H:i') . ' WIB</td>
                        <td colspan="2"></td>
                    </tr>
                </table>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 40%;">Item / Deskripsi</th>
                        <th style="width: 20%;">Sumber</th>
                        <th style="width: 15%;">' . ($role === 'borrower' ? 'Pemberi' : 'Peminjam') . '</th>
                        <th style="width: 20%; text-align: right;">Jumlah Angsuran</th>
                    </tr>
                </thead>
                <tbody>';

        $no = 1;
        foreach ($items as $item) {
            $sourceLabel = ($item['source_type'] === 'member_loan') ? 'Pinjaman Anggota' : 'Pinjaman Pribadi';
            $partner = ($role === 'borrower') ? $item['lender_name'] : $item['borrower_name'];
            
            $html .= '
                    <tr>
                        <td>' . $no++ . '</td>
                        <td>' . esc($item['description']) . '</td>
                        <td>' . $sourceLabel . '</td>
                        <td>' . esc($partner) . '</td>
                        <td class="text-right">Rp ' . number_format($item['amount'], 0, ',', '.') . '</td>
                    </tr>';
        }

        $html .= '
                    <tr class="total-row">
                        <td colspan="4" class="text-right">Total Tagihan Bulanan:</td>
                        <td class="text-right">Rp ' . number_format($totalAmount, 0, ',', '.') . '</td>
                    </tr>
                </tbody>
            </table>';

        if (!empty($proofImages)) {
            $html .= '
            <div class="proof-section">
                <div class="proof-title">Bukti Pelunasan / Transfer</div>';
            foreach ($proofImages as $pi) {
                $html .= '
                <div class="proof-container">
                    <div style="font-size: 9pt; font-weight: bold; margin-bottom: 5px; color: #555;">
                        Dari: ' . esc($pi['from']) . ' &rarr; Kepada: ' . esc($pi['to']) . ' <br>
                        Sejumlah: Rp ' . number_format($pi['amount'], 0, ',', '.') . '
                    </div>
                    <img class="proof-img" src="' . $pi['base64'] . '" alt="Bukti Transfer">
                </div>';
            }
            $html .= '
            </div>';
        }

        $html .= '
            <div class="footer">
                Laporan ini di-generate secara otomatis oleh Sistem Split Bill Keluarga. Hak Cipta &copy; ' . date('Y') . '.
            </div>
        </body>
        </html>';

        // Instantiate and build Dompdf
        $dompdf = new \Dompdf\Dompdf([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="laporan_tagihan_' . date('Y_m', strtotime($monthCol)) . '.pdf"')
            ->setBody($dompdf->output());
    }

    public function printAllPdf()
    {
        $userId = user_id();
        $role = $this->request->getGet('role') ?? 'borrower';
        $tripId = $this->request->getGet('trip_id');

        // Optional filters matching the client-side JQuery filters
        $filterMonth  = $this->request->getGet('filter_month');
        $filterTrip   = $this->request->getGet('filter_trip');
        $filterLender = $this->request->getGet('filter_lender');
        $filterStatus = $this->request->getGet('filter_status');

        if (empty($tripId)) {
            return $this->response->setBody("Error: Trip ID tidak boleh kosong.")->setStatusCode(400);
        }

        // Check trip access
        $currentMembership = $this->checkTripAccess((int)$tripId);
        if (!$currentMembership) {
            return $this->response->setBody("Error: Anda tidak memiliki akses ke trip ini.")->setStatusCode(403);
        }

        $selectedTrip = $this->tripModel->find($tripId);
        if (!$selectedTrip) {
            return $this->response->setBody("Error: Trip tidak ditemukan.")->setStatusCode(404);
        }

        // Get installments visible to user in this trip
        $installments = $this->installmentModel->getVisibleByUser($userId, (int)$tripId);
        $userIdInt = (int)$userId;

        // Populate trip_name since getVisibleByUser doesn't load it
        foreach ($installments as &$inst) {
            $inst['trip_name'] = $selectedTrip['name'];
        }
        unset($inst);

        // Filter by role
        $installments = array_values(array_filter($installments, function($inst) use ($userIdInt, $role) {
            if ($role === 'borrower') {
                return (int)$inst['borrower_user_id'] === $userIdInt;
            } else {
                return (int)$inst['lender_user_id'] === $userIdInt;
            }
        }));

        if (empty($installments)) {
            return $this->response->setBody("Error: Tidak ada data cicilan ditemukan.")->setStatusCode(404);
        }

        // Get installment payments
        $installmentIds = array_column($installments, 'id');
        $allPayments = $this->paymentModel
            ->whereIn('installment_id', $installmentIds)
            ->orderBy('due_date', 'ASC')
            ->findAll();

        // Group payments by installment ID
        $paymentMap = [];
        $monthSet = [];
        foreach ($allPayments as $p) {
            $paymentMap[$p['installment_id']][$p['due_date']] = $p;
            $monthSet[$p['due_date']] = $p['due_date'];
        }
        ksort($monthSet);
        $monthColumns = array_values($monthSet);

        // Fetch group payment history
        $groupPayments = $this->groupPaymentModel->getHistoryByTrip((int)$tripId, $userId);
        $groupPaymentMap = [];
        foreach ($groupPayments as $gp) {
            $key = ($gp['lender_user_id'] ?? 'null') . '|' . $gp['borrower_user_id'] . '|' . $gp['source_type'] . '|' . $gp['due_month'];
            $groupPaymentMap[$key] = $gp;
        }

        // Apply filters in PHP matching JS client-side filter
        $filteredInstallments = [];
        foreach ($installments as &$inst) {
            $inst['payments'] = $paymentMap[$inst['id']] ?? [];

            // 1. Filter by Status
            if (!empty($filterStatus)) {
                if ($filterStatus === 'active' && $inst['status'] !== 'active') continue;
                if ($filterStatus === 'completed' && $inst['status'] !== 'completed') continue;
                if ($filterStatus === 'unpaid') {
                    $hasUnpaid = false;
                    foreach ($inst['payments'] as $p) {
                        if ($p['status'] !== 'paid') {
                            $hasUnpaid = true;
                            break;
                        }
                    }
                    if (!$hasUnpaid) continue;
                }
            }

            // 2. Filter by Trip Name
            if (!empty($filterTrip) && strtolower($inst['trip_name']) !== strtolower($filterTrip)) {
                continue;
            }

            // 3. Filter by Lender / Source Name
            if (!empty($filterLender)) {
                $isLoan = ($inst['source_type'] === 'member_loan');
                $lenderName = $isLoan ? ($inst['lender_name'] ?? 'Anggota') : 'Pinjaman Pribadi';
                if (strtolower($lenderName) !== strtolower($filterLender)) {
                    continue;
                }
            }

            // 4. Filter by Month Column
            if (!empty($filterMonth) && !isset($inst['payments'][$filterMonth])) {
                continue;
            }

            $filteredInstallments[] = $inst;
        }

        if (!empty($filterMonth)) {
            $monthColumns = [$filterMonth];
        }

        if (empty($filteredInstallments)) {
            return $this->response->setBody("Error: Tidak ada data cicilan setelah filter diterapkan.")->setStatusCode(404);
        }

        // Calculate columns totals
        $monthlyTotals = array_fill_keys($monthColumns, 0);
        $grandTotalAll = 0;
        foreach ($filteredInstallments as $inst) {
            $grandTotalAll += (int)$inst['total_amount'];
            foreach ($monthColumns as $col) {
                if (isset($inst['payments'][$col])) {
                    $monthlyTotals[$col] += (int)$inst['payments'][$col]['due_amount'];
                }
            }
        }

        // Render HTML for Dompdf Landscape
        $userObj = user();
        $userFullname = !empty($userObj->fullname) ? $userObj->fullname : $userObj->username;

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Laporan Proyeksi Cicilan - ' . esc($selectedTrip['name']) . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    color: #333;
                    line-height: 1.3;
                    font-size: 9pt;
                }
                .header-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                }
                .header-logo {
                    font-size: 16pt;
                    font-weight: bold;
                    color: #007bff;
                }
                .header-title {
                    text-align: right;
                    font-size: 12pt;
                    font-weight: bold;
                    color: #555;
                }
                .info-section {
                    margin-bottom: 15px;
                }
                .info-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .info-table td {
                    padding: 3px 0;
                    vertical-align: top;
                }
                .label {
                    color: #666;
                    width: 120px;
                    font-weight: bold;
                }
                .grid-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                    margin-bottom: 20px;
                }
                .grid-table th {
                    background-color: #f1f3f5;
                    border: 1px solid #dee2e6;
                    padding: 6px;
                    font-weight: bold;
                    text-align: left;
                    font-size: 8.5pt;
                }
                .grid-table td {
                    padding: 6px;
                    border: 1px solid #dee2e6;
                    font-size: 8.5pt;
                    vertical-align: middle;
                }
                .text-right {
                    text-align: right;
                }
                .text-center {
                    text-align: center;
                }
                .total-row {
                    font-weight: bold;
                    background-color: #f8f9fa;
                }
                .status-lunas {
                    color: #28a745;
                    font-weight: bold;
                }
                .status-belum-lunas {
                    color: #6c757d;
                }
                .badge {
                    display: inline-block;
                    padding: 2px 5px;
                    font-size: 7.5pt;
                    font-weight: bold;
                    border-radius: 3px;
                    color: #fff;
                }
                .badge-primary { background-color: #007bff; }
                .badge-success { background-color: #28a745; }
                .badge-secondary { background-color: #6c757d; }
                .footer {
                    margin-top: 20px;
                    font-size: 7.5pt;
                    color: #777;
                    text-align: center;
                    border-top: 1px solid #eee;
                    padding-top: 6px;
                }
            </style>
        </head>
        <body>
            <table class="header-table">
                <tr>
                    <td class="header-logo">Split Bill Keluarga</td>
                    <td class="header-title">LAPORAN PROYEKSI & RINCIAN CICILAN</td>
                </tr>
            </table>

            <div class="info-section">
                <table class="info-table">
                    <tr>
                        <td class="label">Nama Kegiatan:</td>
                        <td>' . esc($selectedTrip['name']) . '</td>
                        <td class="label" style="text-align: right;">Peran Tampilan:</td>
                        <td style="text-align: right; width: 220px;">' . ($role === 'borrower' ? 'Sebagai Peminjam (Hutang)' : 'Sebagai Pemberi (Piutang)') . '</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Cetak:</td>
                        <td>' . date('d F Y H:i') . ' WIB</td>
                        <td class="label" style="text-align: right;">Filter Diterapkan:</td>
                        <td style="text-align: right; font-style: italic;">
                            ' . (!empty($filterStatus) ? 'Status: ' . esc($filterStatus) . '; ' : '') . '
                            ' . (!empty($filterLender) ? 'Lender: ' . esc($filterLender) . '; ' : '') . '
                            ' . (!empty($filterMonth) ? 'Bulan: ' . date('M Y', strtotime($filterMonth)) . '; ' : 'Semua Bulan') . '
                        </td>
                    </tr>
                </table>
            </div>

            <table class="grid-table">
                <thead>
                    <tr>
                        <th>Uraian Cicilan</th>
                        <th>Jenis</th>
                        <th>' . ($role === 'borrower' ? 'Pemberi / Sumber' : 'Peminjam') . '</th>
                        <th class="text-right">Total Pinjaman</th>';
        foreach ($monthColumns as $col) {
            $html .= '<th class="text-right">' . date('M\'y', strtotime($col)) . '</th>';
        }
        $html .= '
                    </tr>
                </thead>
                <tbody>';

        foreach ($filteredInstallments as $inst) {
            $isLoan = ($inst['source_type'] === 'member_loan');
            $partner = ($role === 'borrower') 
                ? ($isLoan ? $inst['lender_name'] : 'Pinjaman Pribadi')
                : $inst['borrower_name'];
            $sourceLabel = $isLoan ? 'Pinjaman Anggota' : 'Pinjaman Pribadi';
            $badgeClass = $isLoan ? 'badge-primary' : 'badge-success';

            $html .= '
                    <tr>
                        <td><strong>' . esc($inst['description']) . '</strong></td>
                        <td><span class="badge ' . $badgeClass . '">' . $sourceLabel . '</span></td>
                        <td>' . esc($partner) . '</td>
                        <td class="text-right">Rp ' . number_format($inst['total_amount'], 0, ',', '.') . '</td>';

            foreach ($monthColumns as $col) {
                $payment = $inst['payments'][$col] ?? null;
                if ($payment) {
                    if ($payment['status'] === 'paid') {
                        $html .= '<td class="text-right status-lunas">Lunas<br><span style="font-size: 7.5pt; color: #555;">Rp ' . number_format($payment['due_amount'], 0, ',', '.') . '</span></td>';
                    } else {
                        $html .= '<td class="text-right status-belum-lunas">Belum<br><span style="font-size: 7.5pt; color: #dc3545; font-weight: bold;">Rp ' . number_format($payment['due_amount'], 0, ',', '.') . '</span></td>';
                    }
                } else {
                    $html .= '<td class="text-right text-muted">—</td>';
                }
            }
            $html .= '
                    </tr>';
        }

        $html .= '
                    <tr class="total-row">
                        <td colspan="3" class="text-right">Total Tagihan Bulanan:</td>
                        <td class="text-right">Rp ' . number_format($grandTotalAll, 0, ',', '.') . '</td>';
        foreach ($monthColumns as $col) {
            $html .= '<td class="text-right">Rp ' . number_format($monthlyTotals[$col], 0, ',', '.') . '</td>';
        }
        $html .= '
                    </tr>
                </tbody>
            </table>

            <div class="footer">
                Laporan Proyeksi Rekapitulasi Cicilan - Dicetak secara otomatis oleh Sistem Split Bill Keluarga. Hak Cipta &copy; ' . date('Y') . '.
            </div>
        </body>
        </html>';

        // Render PDF in Landscape mode
        $dompdf = new \Dompdf\Dompdf([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="laporan_rekap_cicilan.pdf"')
            ->setBody($dompdf->output());
    }
}
