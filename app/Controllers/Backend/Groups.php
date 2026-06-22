<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\GroupModel;
use App\Models\GroupMemberModel;
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

        $group = $this->groupModel->find($groupId);
        if (!$group) {
            return redirect()->to('backend/groups')->with('error', 'Grup tidak ditemukan.');
        }

        // 2. Dapatkan seluruh anggota grup saat ini
        $members = $this->memberModel->select('group_members.*, users.username, users.email')
                                     ->join('users', 'users.id = group_members.user_id')
                                     ->where('group_members.group_id', $groupId)
                                     ->findAll();

        // 3. Cari user lain di sistem yang belum bergabung ke grup ini (untuk di-invite)
        $memberUserIds = array_column($members, 'user_id');
        
        $allUsersQuery = $this->userModel->select('id, username, email');
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
}
