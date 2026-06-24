<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\TripModel;
use App\Models\PeriodModel;
use App\Models\PeriodActiveMemberModel;
use App\Models\GroupModel;
use App\Models\GroupMemberModel;
use App\Models\TransactionModel;
use App\Models\SettlementModel;

class Trips extends BaseController
{
    protected $tripModel;
    protected $periodModel;
    protected $activeMemberModel;
    protected $groupModel;
    protected $groupMemberModel;

    public function __construct()
    {
        $this->tripModel = new TripModel();
        $this->periodModel = new PeriodModel();
        $this->activeMemberModel = new PeriodActiveMemberModel();
        $this->groupModel = new GroupModel();
        $this->groupMemberModel = new GroupMemberModel();
    }

    /**
     * Check if user is a member of a group
     */
    protected function checkMembership(int $groupId): ?array
    {
        return $this->groupMemberModel->where('group_id', $groupId)
                                       ->where('user_id', user_id())
                                       ->first();
    }

    /**
     * Halaman daftar trip
     */
    public function index()
    {
        $userId = user_id();

        // Mengambil semua trip dari grup-grup di mana user adalah anggotanya
        $trips = $this->tripModel->select('trips.*, groups.name as group_name')
                                 ->join('groups', 'groups.id = trips.group_id')
                                 ->join('group_members', 'group_members.group_id = groups.id')
                                 ->where('group_members.user_id', $userId)
                                 ->findAll();

        $data = [
            'pageTitle' => 'Kegiatan & Periode',
            'trips'     => $trips,
            'user'      => user()
        ];

        return view('backend/trips/index', $data);
    }

    /**
     * Form tambah trip baru
     */
    public function create()
    {
        $userId = user_id();
        
        // Ambil grup di mana user adalah admin
        $myGroups = $this->groupModel->select('groups.*')
                                     ->join('group_members', 'group_members.group_id = groups.id')
                                     ->where('group_members.user_id', $userId)
                                     ->where('group_members.role', 'admin')
                                     ->findAll();

        $data = [
            'pageTitle' => 'Buat Kegiatan Baru',
            'groups'    => $myGroups,
            'user'      => user()
        ];

        return view('backend/trips/create', $data);
    }

    /**
     * Simpan trip baru
     */
    public function store()
    {
        $rules = [
            'group_id'   => 'required|numeric',
            'name'       => 'required|min_length[3]|max_length[100]',
            'start_date' => 'permit_empty|valid_date[Y-m-d]',
            'end_date'   => 'permit_empty|valid_date[Y-m-d]',
            'notes'      => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $groupId = (int)$this->request->getPost('group_id');
        
        // Cek apakah user adalah admin grup tersebut
        $membership = $this->checkMembership($groupId);
        if (!$membership || $membership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat menambahkan kegiatan baru.');
        }

        $this->tripModel->insert([
            'group_id'   => $groupId,
            'name'       => $this->request->getPost('name'),
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date'   => $this->request->getPost('end_date') ?: null,
            'notes'      => $this->request->getPost('notes') ?: null,
        ]);

        return redirect()->to('backend/trips')->with('success', 'Kegiatan baru berhasil dibuat.');
    }

    /**
     * Detail Trip & Kelola Periode
     */
    public function detail(int $tripId)
    {
        $trip = $this->tripModel->find($tripId);
        if (!$trip) {
            return redirect()->to('backend/trips')->with('error', 'Kegiatan tidak ditemukan.');
        }

        // Cek membership grup dari trip ini
        $membership = $this->checkMembership((int)$trip['group_id']);
        if (!$membership) {
            return redirect()->to('backend/trips')->with('error', 'Anda tidak memiliki akses ke kegiatan ini.');
        }

        $group = $this->groupModel->find($trip['group_id']);

        // Ambil list periode dalam trip ini
        $periods = $this->periodModel->where('trip_id', $tripId)
                                     ->orderBy('created_at', 'ASC')
                                     ->findAll();

        // Ambil anggota grup saat ini
        $groupMembers = $this->groupMemberModel->select('group_members.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as username, users.email')
                                               ->join('users', 'users.id = group_members.user_id')
                                               ->where('group_members.group_id', $trip['group_id'])
                                               ->findAll();

        // Untuk setiap periode, dapatkan list user ID anggota aktif
        $activeMembersPerPeriod = [];
        foreach ($periods as $p) {
            $actives = $this->activeMemberModel->where('period_id', $p['id'])->findAll();
            $activeMembersPerPeriod[$p['id']] = array_column($actives, 'user_id');
        }

        $data = [
            'pageTitle'              => 'Detail Kegiatan: ' . esc($trip['name']),
            'trip'                   => $trip,
            'group'                  => $group,
            'periods'                => $periods,
            'groupMembers'           => $groupMembers,
            'activeMembersPerPeriod' => $activeMembersPerPeriod,
            'currentMembership'      => $membership,
            'user'                   => user(),
            'breadcrumb'             => [
                ['title' => 'Kegiatan & Periode', 'url' => 'backend/trips'],
                ['title' => 'Detail Kegiatan', 'url' => 'backend/trips/detail/' . $tripId]
            ]
        ];

        return view('backend/trips/detail', $data);
    }

    /**
     * Tambah periode bulanan ke trip (Hanya Admin Grup)
     */
    public function addPeriod(int $tripId)
    {
        $trip = $this->tripModel->find($tripId);
        if (!$trip) {
            return redirect()->back()->with('error', 'Kegiatan tidak ditemukan.');
        }

        // Cek membership grup
        $membership = $this->checkMembership((int)$trip['group_id']);
        if (!$membership || $membership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat menambahkan periode.');
        }

        $rules = [
            'label'      => 'required|min_length[3]|max_length[50]',
            'start_date' => 'permit_empty|valid_date[Y-m-d]',
            'end_date'   => 'permit_empty|valid_date[Y-m-d]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Simpan periode
        $periodId = $this->periodModel->insert([
            'trip_id'    => $tripId,
            'label'      => $this->request->getPost('label'),
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date'   => $this->request->getPost('end_date') ?: null,
        ]);

        // 2. Set seluruh anggota grup aktif secara default
        $groupMembers = $this->groupMemberModel->where('group_id', $trip['group_id'])->findAll();
        foreach ($groupMembers as $gm) {
            $this->activeMemberModel->insert([
                'period_id'  => $periodId,
                'user_id'    => $gm['user_id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal membuat periode baru.');
        }

        return redirect()->to('backend/trips/detail/' . $tripId)->with('success', 'Periode baru berhasil ditambahkan.');
    }

    /**
     * Simpan status keanggotaan aktif per periode (Hanya Admin Grup)
     */
    public function saveActiveMembers(int $periodId)
    {
        $period = $this->periodModel->find($periodId);
        if (!$period) {
            return redirect()->back()->with('error', 'Periode tidak ditemukan.');
        }

        $trip = $this->tripModel->find($period['trip_id']);
        
        // Cek membership grup
        $membership = $this->checkMembership((int)$trip['group_id']);
        if (!$membership || $membership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat mengelola anggota aktif.');
        }

        $activeUserIds = $this->request->getPost('active_users') ?: [];

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Hapus status anggota aktif lama untuk periode ini
        $this->activeMemberModel->where('period_id', $periodId)->delete();

        // 2. Tambah yang dicentang
        foreach ($activeUserIds as $uId) {
            $this->activeMemberModel->insert([
                'period_id'  => $periodId,
                'user_id'    => (int)$uId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memperbarui anggota aktif.');
        }

        return redirect()->to('backend/trips/detail/' . $trip['id'])->with('success', 'Keanggotaan aktif periode berhasil diperbarui.');
    }

    /**
     * Preview dampak penghapusan trip (JSON)
     */
    public function deletePreview(int $tripId)
    {
        $trip = $this->tripModel->find($tripId);
        if (!$trip) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kegiatan tidak ditemukan.'
            ])->setStatusCode(404);
        }

        // Cek apakah user adalah admin dari grup trip ini
        $membership = $this->checkMembership((int)$trip['group_id']);
        if (!$membership || $membership['role'] !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Hanya admin grup yang dapat mengakses aksi ini.'
            ])->setStatusCode(403);
        }

        $periodModel = new PeriodModel();
        $transactionModel = new TransactionModel();
        $settlementModel = new SettlementModel();

        // 1. Periods
        $periodsCount = $periodModel->where('trip_id', $tripId)->countAllResults();

        // 2. Transactions
        $transactionsCount = $transactionModel->where('trip_id', $tripId)->countAllResults();

        // 3. Settlements
        $settlementsCount = $settlementModel->where('trip_id', $tripId)->countAllResults();

        // 4. Files
        $receiptFilesCount = $transactionModel->where('trip_id', $tripId)
                                              ->where("receipt_image IS NOT NULL AND receipt_image != ''")
                                              ->countAllResults();

        $proofFilesCount = $settlementModel->where('trip_id', $tripId)
                                            ->where("proof_image IS NOT NULL AND proof_image != ''")
                                            ->countAllResults();

        $totalFilesCount = $receiptFilesCount + $proofFilesCount;

        return $this->response->setJSON([
            'success'      => true,
            'trip_name'    => $trip['name'],
            'periods'      => $periodsCount,
            'transactions' => $transactionsCount,
            'settlements'  => $settlementsCount,
            'files'        => $totalFilesCount
        ]);
    }

    /**
     * Hapus trip beserta seluruh data terkait & berkas fisik
     */
    public function delete(int $tripId)
    {
        $trip = $this->tripModel->find($tripId);
        if (!$trip) {
            return redirect()->to('backend/trips')->with('error', 'Kegiatan tidak ditemukan.');
        }

        // Cek apakah user adalah admin dari grup trip ini
        $membership = $this->checkMembership((int)$trip['group_id']);
        if (!$membership || $membership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat menghapus kegiatan.');
        }

        $transactionModel = new TransactionModel();
        $settlementModel = new SettlementModel();

        // 1. Hapus berkas nota transaksi
        $transactions = $transactionModel->where('trip_id', $tripId)
                                          ->where("receipt_image IS NOT NULL AND receipt_image != ''")
                                          ->findAll();

        foreach ($transactions as $t) {
            $filePath = FCPATH . $t['receipt_image'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }

        // 2. Hapus berkas bukti transfer
        $settlements = $settlementModel->where('trip_id', $tripId)
                                        ->where("proof_image IS NOT NULL AND proof_image != ''")
                                        ->findAll();

        foreach ($settlements as $s) {
            $filePath = FCPATH . $s['proof_image'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }

        // 3. Hapus database (cascade akan otomatis menghapus periode, transaksi, dll)
        $this->tripModel->delete($tripId);

        return redirect()->to('backend/trips')->with('success', 'Kegiatan beserta seluruh data dan berkas terkait berhasil dihapus secara bersih.');
    }

    /**
     * Update data trip (Hanya Admin Grup dari trip terkait yang bisa)
     */
    public function update(int $tripId)
    {
        $trip = $this->tripModel->find($tripId);
        if (!$trip) {
            return redirect()->to('backend/trips')->with('error', 'Kegiatan tidak ditemukan.');
        }

        // Cek membership grup
        $membership = $this->checkMembership((int)$trip['group_id']);
        if (!$membership || $membership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat mengubah detail kegiatan.');
        }

        $rules = [
            'name'       => 'required|min_length[3]|max_length[100]',
            'start_date' => 'permit_empty|valid_date[Y-m-d]',
            'end_date'   => 'permit_empty|valid_date[Y-m-d]',
            'notes'      => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('trip_errors', $this->validator->getErrors());
        }

        $this->tripModel->update($tripId, [
            'name'       => $this->request->getPost('name'),
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date'   => $this->request->getPost('end_date') ?: null,
            'notes'      => $this->request->getPost('notes') ?: null,
            'group_id'   => $trip['group_id']
        ]);

        return redirect()->to('backend/trips/detail/' . $tripId)->with('success', 'Detail kegiatan berhasil diperbarui.');
    }

    /**
     * Update data periode (Hanya Admin Grup yang bisa)
     */
    public function updatePeriod(int $periodId)
    {
        $periodModel = new PeriodModel();
        $period = $periodModel->find($periodId);
        if (!$period) {
            return redirect()->back()->with('error', 'Periode tidak ditemukan.');
        }

        $trip = $this->tripModel->find($period['trip_id']);
        
        // Cek membership grup
        $membership = $this->checkMembership((int)$trip['group_id']);
        if (!$membership || $membership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat mengubah periode.');
        }

        $rules = [
            'label'      => 'required|min_length[3]|max_length[50]',
            'start_date' => 'permit_empty|valid_date[Y-m-d]',
            'end_date'   => 'permit_empty|valid_date[Y-m-d]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $periodModel->update($periodId, [
            'label'      => $this->request->getPost('label'),
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date'   => $this->request->getPost('end_date') ?: null,
            'trip_id'    => $period['trip_id']
        ]);

        return redirect()->to('backend/trips/detail/' . $trip['id'])->with('success', 'Periode berhasil diperbarui.');
    }

    /**
     * Preview dampak penghapusan periode (JSON)
     */
    public function deletePeriodPreview(int $periodId)
    {
        $periodModel = new PeriodModel();
        $period = $periodModel->find($periodId);
        if (!$period) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Periode tidak ditemukan.'
            ])->setStatusCode(404);
        }

        $trip = $this->tripModel->find($period['trip_id']);

        // Cek membership grup
        $membership = $this->checkMembership((int)$trip['group_id']);
        if (!$membership || $membership['role'] !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Hanya admin grup yang dapat mengakses aksi ini.'
            ])->setStatusCode(403);
        }

        $transactionModel = new TransactionModel();
        $settlementModel = new SettlementModel();

        // 1. Transactions
        $transactionsCount = $transactionModel->where('period_id', $periodId)->countAllResults();

        // 2. Settlements
        $settlementsCount = $settlementModel->where('period_id', $periodId)->countAllResults();

        // 3. Files (receipts + proofs)
        $receiptFilesCount = $transactionModel->where('period_id', $periodId)
                                              ->where("receipt_image IS NOT NULL AND receipt_image != ''")
                                              ->countAllResults();

        $proofFilesCount = $settlementModel->where('period_id', $periodId)
                                            ->where("proof_image IS NOT NULL AND proof_image != ''")
                                            ->countAllResults();

        $totalFilesCount = $receiptFilesCount + $proofFilesCount;

        return $this->response->setJSON([
            'success'      => true,
            'label'        => $period['label'],
            'transactions' => $transactionsCount,
            'settlements'  => $settlementsCount,
            'files'        => $totalFilesCount
        ]);
    }

    /**
     * Hapus periode beserta transaksi & settlement terkait & file fisiknya
     */
    public function deletePeriod(int $periodId)
    {
        $periodModel = new PeriodModel();
        $period = $periodModel->find($periodId);
        if (!$period) {
            return redirect()->back()->with('error', 'Periode tidak ditemukan.');
        }

        $trip = $this->tripModel->find($period['trip_id']);

        // Cek membership grup
        $membership = $this->checkMembership((int)$trip['group_id']);
        if (!$membership || $membership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat menghapus periode.');
        }

        $transactionModel = new TransactionModel();
        $settlementModel = new SettlementModel();

        // 1. Hapus berkas nota transaksi
        $transactions = $transactionModel->where('period_id', $periodId)
                                          ->where("receipt_image IS NOT NULL AND receipt_image != ''")
                                          ->findAll();

        foreach ($transactions as $t) {
            $filePath = FCPATH . $t['receipt_image'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }

        // 2. Hapus berkas bukti transfer settlement
        $settlements = $settlementModel->where('period_id', $periodId)
                                        ->where("proof_image IS NOT NULL AND proof_image != ''")
                                        ->findAll();

        foreach ($settlements as $s) {
            $filePath = FCPATH . $s['proof_image'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }

        // 3. Hapus database (cascade akan otomatis menghapus transaksi, settlement dll)
        $periodModel->delete($periodId);

        return redirect()->to('backend/trips/detail/' . $trip['id'])->with('success', 'Periode beserta seluruh transaksi, settlement, dan berkas terkait berhasil dihapus secara bersih.');
    }

    /**
     * Toggle status periode antara 'open' dan 'settled' (Hanya Admin Grup)
     */
    public function togglePeriodStatus(int $periodId)
    {
        $periodModel = new PeriodModel();
        $period = $periodModel->find($periodId);
        if (!$period) {
            return redirect()->back()->with('error', 'Periode tidak ditemukan.');
        }

        $trip = $this->tripModel->find($period['trip_id']);

        // Cek membership grup — hanya admin yang boleh
        $membership = $this->checkMembership((int)$trip['group_id']);
        if (!$membership || $membership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat mengubah status periode.');
        }

        // Toggle status
        $currentStatus = $period['status'] ?? 'open';
        $newStatus     = ($currentStatus === 'open') ? 'settled' : 'open';

        $periodModel->update($periodId, ['status' => $newStatus]);

        $label = esc($period['label']);
        if ($newStatus === 'settled') {
            $msg = "Periode \"{$label}\" berhasil ditutup (Settled). Transaksi baru tidak dapat ditambahkan ke periode ini.";
        } else {
            $msg = "Periode \"{$label}\" berhasil dibuka kembali (Open). Transaksi baru dapat ditambahkan kembali.";
        }

        return redirect()->to('backend/trips/detail/' . $trip['id'])->with('success', $msg);
    }
}
