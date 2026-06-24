<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\GroupModel;
use App\Models\GroupMemberModel;
use App\Models\TripModel;
use App\Models\PeriodModel;
use App\Models\TransactionModel;
use App\Models\SettlementModel;
use Myth\Auth\Models\UserModel;

class Groups extends BaseController
{
    protected $groupModel;
    protected $memberModel;
    protected $userModel;

    public function __construct()
    {
        $this->groupModel = new GroupModel();
        $this->memberModel = new GroupMemberModel();
        $this->userModel = new UserModel();
    }

    /**
     * Check if the current user is a member of the group
     */
    protected function checkMembership(int $groupId): ?array
    {
        $userId = user_id();
        $membership = $this->memberModel->where('group_id', $groupId)
                                         ->where('user_id', $userId)
                                         ->first();
        return $membership;
    }

    /**
     * Halaman daftar group yang diikuti
     */
    public function index()
    {
        $userId = user_id();
        
        // Ambil grup-grup di mana user adalah anggotanya
        $groups = $this->groupModel->select('groups.*, group_members.role, group_members.is_active')
                                   ->join('group_members', 'group_members.group_id = groups.id')
                                   ->where('group_members.user_id', $userId)
                                   ->findAll();

        // Ambil anggota untuk setiap grup
        foreach ($groups as &$group) {
            $group['members'] = $this->memberModel->select('group_members.*, users.username, users.fullname')
                                                 ->join('users', 'users.id = group_members.user_id')
                                                 ->where('group_members.group_id', $group['id'])
                                                 ->findAll();
        }
        unset($group);

        $data = [
            'pageTitle' => 'Groups Saya',
            'groups'    => $groups,
            'user'      => user()
        ];

        return view('backend/groups/index', $data);
    }

    /**
     * Form tambah group baru
     */
    public function create()
    {
        $data = [
            'pageTitle' => 'Buat Group Baru',
            'user'      => user()
        ];
        return view('backend/groups/create', $data);
    }

    /**
     * Simpan group baru
     */
    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = user_id();
        $db = \Config\Database::connect();
        
        $db->transStart();

        // 1. Simpan group
        $groupId = $this->groupModel->insert([
            'name'       => $this->request->getPost('name'),
            'created_by' => $userId
        ]);

        // 2. Daftarkan pembuat sebagai Admin di group_members
        $this->memberModel->insert([
            'group_id'  => $groupId,
            'user_id'   => $userId,
            'role'      => 'admin',
            'is_active' => 1
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat grup baru.');
        }

        return redirect()->to('backend/groups')->with('success', 'Grup berhasil dibuat.');
    }

    /**
     * Detail Group & Kelola Anggota
     */
    public function detail(int $groupId)
    {
        // 1. Cek membership (isolasi data)
        $currentMembership = $this->checkMembership($groupId);
        if (!$currentMembership) {
            return redirect()->to('backend/groups')->with('error', 'Anda tidak memiliki akses ke grup tersebut.');
        }

        $group = $this->groupModel->select('groups.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as creator_name')
                                  ->join('users', 'users.id = groups.created_by', 'left')
                                  ->find($groupId);
        if (!$group) {
            return redirect()->to('backend/groups')->with('error', 'Grup tidak ditemukan.');
        }

        // 2. Dapatkan seluruh anggota grup saat ini
        $members = $this->memberModel->select('group_members.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as username, users.email')
                                     ->join('users', 'users.id = group_members.user_id')
                                     ->where('group_members.group_id', $groupId)
                                     ->findAll();

        // 3. Cari user lain di sistem yang belum bergabung ke grup ini (untuk di-invite)
        $memberUserIds = array_column($members, 'user_id');
        
        $allUsersQuery = $this->userModel->select('id, COALESCE(NULLIF(users.fullname, \'\'), users.username) as username, email');
        if (!empty($memberUserIds)) {
            $allUsersQuery->whereNotIn('id', $memberUserIds);
        }
        $availableUsers = $allUsersQuery->asArray()->findAll();

        $data = [
            'pageTitle'         => 'Detail Group: ' . esc($group['name']),
            'group'             => $group,
            'members'           => $members,
            'availableUsers'    => $availableUsers,
            'currentMembership' => $currentMembership,
            'user'              => user(),
            'breadcrumb'        => [
                ['title' => 'Groups Saya', 'url' => 'backend/groups'],
                ['title' => 'Detail', 'url' => 'backend/groups/detail/' . $groupId]
            ]
        ];

        return view('backend/groups/detail', $data);
    }

    /**
     * Tambah anggota ke grup (Hanya Group Admin yang bisa)
     */
    public function addMember(int $groupId)
    {
        // 1. Cek membership
        $currentMembership = $this->checkMembership($groupId);
        if (!$currentMembership || $currentMembership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat menambah anggota baru.');
        }

        $userIdToAdd = $this->request->getPost('user_id');
        $role = $this->request->getPost('role') ?? 'member';

        if (empty($userIdToAdd)) {
            return redirect()->back()->with('error', 'Pilih pengguna terlebih dahulu.');
        }

        // Cek apakah user sudah terdaftar di grup
        $existing = $this->memberModel->where('group_id', $groupId)
                                      ->where('user_id', $userIdToAdd)
                                      ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Pengguna sudah bergabung dalam grup ini.');
        }

        $this->memberModel->insert([
            'group_id'  => $groupId,
            'user_id'   => $userIdToAdd,
            'role'      => $role,
            'is_active' => 1
        ]);

        return redirect()->to('backend/groups/detail/' . $groupId)->with('success', 'Anggota berhasil ditambahkan.');
    }

    /**
     * Update role anggota grup (admin ↔ member). Hanya Group Admin yang bisa.
     */
    public function updateRole(int $groupId, int $targetUserId, string $newRole)
    {
        // 1. Validasi role yang diberikan
        if (!in_array($newRole, ['admin', 'member'])) {
            return redirect()->back()->with('error', 'Role tidak valid.');
        }

        // 2. Cek apakah current user adalah admin grup
        $currentMembership = $this->checkMembership($groupId);
        if (!$currentMembership || $currentMembership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat mengubah role anggota.');
        }

        // 3. Cek target user memang ada di grup
        $targetMembership = $this->memberModel
                                  ->where('group_id', $groupId)
                                  ->where('user_id', $targetUserId)
                                  ->first();

        if (!$targetMembership) {
            return redirect()->back()->with('error', 'Anggota tidak ditemukan di grup ini.');
        }

        // 4. Cegah admin satu-satunya menurunkan dirinya sendiri
        if ($targetUserId === (int)user_id() && $newRole === 'member') {
            $adminCount = $this->memberModel->where('group_id', $groupId)
                                            ->where('role', 'admin')
                                            ->countAllResults();
            if ($adminCount <= 1) {
                return redirect()->back()->with('error', 'Anda tidak bisa menurunkan role diri sendiri karena Anda adalah satu-satunya admin grup.');
            }
        }

        // 5. Update role
        $this->memberModel->where('group_id', $groupId)
                          ->where('user_id', $targetUserId)
                          ->set(['role' => $newRole])
                          ->update();

        $label = ($newRole === 'admin') ? 'Group Admin' : 'Member';
        $targetUser = $this->userModel->asArray()->find($targetUserId);
        $username = $targetUser['username'] ?? 'Anggota';

        return redirect()->to('backend/groups/detail/' . $groupId)
                         ->with('success', "Role {$username} berhasil diubah menjadi {$label}.");
    }

    /**
     * Hapus anggota dari grup (Hanya Group Admin yang bisa)
     */
    public function removeMember(int $groupId, int $userIdToRemove)
    {
        // 1. Cek membership saat ini
        $currentMembership = $this->checkMembership($groupId);
        if (!$currentMembership || $currentMembership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat menghapus anggota.');
        }

        // Cegah menghapus diri sendiri jika tidak ada admin lain
        if ($userIdToRemove === (int)user_id()) {
            // Hitung jumlah admin tersisa
            $adminCount = $this->memberModel->where('group_id', $groupId)
                                            ->where('role', 'admin')
                                            ->countAllResults();
            if ($adminCount <= 1) {
                return redirect()->back()->with('error', 'Anda tidak bisa keluar dari grup ini karena Anda adalah satu-satunya admin tersisa.');
            }
        }

        $this->memberModel->where('group_id', $groupId)
                          ->where('user_id', $userIdToRemove)
                          ->delete();

        return redirect()->to('backend/groups/detail/' . $groupId)->with('success', 'Anggota berhasil dihapus.');
    }

    /**
     * Update nama group (Hanya Group Admin yang bisa)
     */
    public function update(int $groupId)
    {
        // 1. Cek membership
        $currentMembership = $this->checkMembership($groupId);
        if (!$currentMembership || $currentMembership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat mengubah informasi grup.');
        }

        $group = $this->groupModel->find($groupId);
        if (!$group) {
            return redirect()->to('backend/groups')->with('error', 'Grup tidak ditemukan.');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newName = $this->request->getPost('name');

        $this->groupModel->update($groupId, [
            'name'       => $newName,
            'created_by' => $group['created_by']
        ]);

        return redirect()->to('backend/groups/detail/' . $groupId)->with('success', 'Nama grup berhasil diperbarui.');
    }

    /**
     * Preview dampak penghapusan grup (JSON)
     */
    public function deletePreview(int $groupId)
    {
        $currentMembership = $this->checkMembership($groupId);
        if (!$currentMembership || $currentMembership['role'] !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Hanya admin grup yang dapat mengakses aksi ini.'
            ])->setStatusCode(403);
        }

        $group = $this->groupModel->find($groupId);
        if (!$group) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Grup tidak ditemukan.'
            ])->setStatusCode(404);
        }

        $tripModel = new TripModel();
        $periodModel = new PeriodModel();
        $transactionModel = new TransactionModel();
        $settlementModel = new SettlementModel();

        // 1. Members
        $membersCount = $this->memberModel->where('group_id', $groupId)->countAllResults();

        // 2. Trips
        $tripsCount = $tripModel->where('group_id', $groupId)->countAllResults();

        // 3. Periods
        $periodsCount = $periodModel->join('trips', 'trips.id = trip_periods.trip_id')
                                    ->where('trips.group_id', $groupId)
                                    ->countAllResults();

        // 4. Transactions
        $transactionsCount = $transactionModel->join('trips', 'trips.id = transactions.trip_id')
                                              ->where('trips.group_id', $groupId)
                                              ->countAllResults();

        // 5. Settlements
        $settlementsCount = $settlementModel->join('trips', 'trips.id = settlements.trip_id')
                                            ->where('trips.group_id', $groupId)
                                            ->countAllResults();

        // 6. Files (receipts + proofs)
        $receiptFilesCount = $transactionModel->join('trips', 'trips.id = transactions.trip_id')
                                              ->where('trips.group_id', $groupId)
                                              ->where("receipt_image IS NOT NULL AND receipt_image != ''")
                                              ->countAllResults();

        $proofFilesCount = $settlementModel->join('trips', 'trips.id = settlements.trip_id')
                                            ->where('trips.group_id', $groupId)
                                            ->where("proof_image IS NOT NULL AND proof_image != ''")
                                            ->countAllResults();

        $totalFilesCount = $receiptFilesCount + $proofFilesCount;

        return $this->response->setJSON([
            'success'      => true,
            'group_name'   => $group['name'],
            'members'      => $membersCount,
            'trips'        => $tripsCount,
            'periods'      => $periodsCount,
            'transactions' => $transactionsCount,
            'settlements'  => $settlementsCount,
            'files'        => $totalFilesCount
        ]);
    }

    /**
     * Hapus grup beserta seluruh data terkait & berkas fisik
     */
    public function delete(int $groupId)
    {
        $currentMembership = $this->checkMembership($groupId);
        if (!$currentMembership || $currentMembership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat menghapus grup.');
        }

        $group = $this->groupModel->find($groupId);
        if (!$group) {
            return redirect()->to('backend/groups')->with('error', 'Grup tidak ditemukan.');
        }

        $transactionModel = new TransactionModel();
        $settlementModel = new SettlementModel();

        // 1. Hapus berkas nota transaksi
        $transactions = $transactionModel->select('transactions.receipt_image')
                                          ->join('trips', 'trips.id = transactions.trip_id')
                                          ->where('trips.group_id', $groupId)
                                          ->where("receipt_image IS NOT NULL AND receipt_image != ''")
                                          ->findAll();

        foreach ($transactions as $t) {
            $filePath = FCPATH . $t['receipt_image'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }

        // 2. Hapus berkas bukti transfer
        $settlements = $settlementModel->select('settlements.proof_image')
                                        ->join('trips', 'trips.id = settlements.trip_id')
                                        ->where('trips.group_id', $groupId)
                                        ->where("proof_image IS NOT NULL AND proof_image != ''")
                                        ->findAll();

        foreach ($settlements as $s) {
            $filePath = FCPATH . $s['proof_image'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }

        // 3. Hapus database (cascade di MySQL akan menangani relasi table)
        $this->groupModel->delete($groupId);

        return redirect()->to('backend/groups')->with('success', 'Grup beserta seluruh data dan berkas terkait berhasil dihapus secara bersih.');
    }
}
