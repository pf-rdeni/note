<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\TripModel;
use App\Models\PeriodModel;
use App\Models\PeriodActiveMemberModel;
use App\Models\GroupModel;
use App\Models\GroupMemberModel;

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
            'pageTitle' => 'Trips & Perjalanan',
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
            'pageTitle' => 'Buat Trip Baru',
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
            return redirect()->back()->with('error', 'Hanya admin grup yang dapat menambahkan trip baru.');
        }

        $this->tripModel->insert([
            'group_id'   => $groupId,
            'name'       => $this->request->getPost('name'),
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date'   => $this->request->getPost('end_date') ?: null,
            'notes'      => $this->request->getPost('notes') ?: null,
        ]);

        return redirect()->to('backend/trips')->with('success', 'Trip perjalanan berhasil dibuat.');
    }

    /**
     * Detail Trip & Kelola Periode
     */
    public function detail(int $tripId)
    {
        $trip = $this->tripModel->find($tripId);
        if (!$trip) {
            return redirect()->to('backend/trips')->with('error', 'Trip tidak ditemukan.');
        }

        // Cek membership grup dari trip ini
        $membership = $this->checkMembership((int)$trip['group_id']);
        if (!$membership) {
            return redirect()->to('backend/trips')->with('error', 'Anda tidak memiliki akses ke trip ini.');
        }

        $group = $this->groupModel->find($trip['group_id']);

        // Ambil list periode dalam trip ini
        $periods = $this->periodModel->where('trip_id', $tripId)
                                     ->orderBy('created_at', 'ASC')
                                     ->findAll();

        // Ambil anggota grup saat ini
        $groupMembers = $this->groupMemberModel->select('group_members.*, users.username, users.email')
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
            'pageTitle'              => 'Detail Trip: ' . esc($trip['name']),
            'trip'                   => $trip,
            'group'                  => $group,
            'periods'                => $periods,
            'groupMembers'           => $groupMembers,
            'activeMembersPerPeriod' => $activeMembersPerPeriod,
            'currentMembership'      => $membership,
            'user'                   => user(),
            'breadcrumb'             => [
                ['title' => 'Trips & Perjalanan', 'url' => 'backend/trips'],
                ['title' => 'Detail Trip', 'url' => 'backend/trips/detail/' . $tripId]
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
            return redirect()->back()->with('error', 'Trip tidak ditemukan.');
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
}
