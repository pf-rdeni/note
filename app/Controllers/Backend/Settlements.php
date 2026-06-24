<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\SettlementModel;
use App\Models\TripModel;
use App\Models\PeriodModel;
use App\Models\GroupMemberModel;
use Myth\Auth\Models\UserModel;

class Settlements extends BaseController
{
    protected $settlementModel;
    protected $tripModel;
    protected $periodModel;
    protected $groupMemberModel;
    protected $userModel;

    public function __construct()
    {
        $this->settlementModel = new SettlementModel();
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
     * Tampilan daftar rekomendasi settlement dan riwayat transfer
     */
    public function index()
    {
        $userId = user_id();

        // 1. Ambil trip yang bisa diakses
        $availableTrips = $this->tripModel->select('trips.*, groups.name as group_name')
                                          ->join('groups', 'groups.id = trips.group_id')
                                          ->join('group_members', 'group_members.group_id = groups.id')
                                          ->where('group_members.user_id', $userId)
                                          ->findAll();

        $session = session();

        if ($this->request->getGet('reset') !== null) {
            $session->remove('set_last_trip_id');
            $session->remove('set_last_period_id');
            $session->remove('set_last_group_id');
            return redirect()->to('backend/settlements');
        }

        $selectedTripId   = $this->request->getGet('trip_id');
        $selectedPeriodId = $this->request->getGet('period_id');
        $selectedGroupId  = $this->request->getGet('group_id');
        $fromGet          = ($this->request->getGet('trip_id') !== null) || ($this->request->getGet('group_id') !== null);

        // Jika tidak ada GET param, coba restore dari session
        if (!$fromGet) {
            $selectedTripId   = $session->get('set_last_trip_id');
            $selectedPeriodId = $session->get('set_last_period_id');
            $selectedGroupId  = $session->get('set_last_group_id');
        }

        // Fallback: gunakan trip pertama jika masih kosong dan tidak ada group terpilih
        if (empty($selectedTripId) && empty($selectedGroupId) && !empty($availableTrips)) {
            $selectedTripId = $availableTrips[0]['id'];
        }

        $selectedTrip = null;
        $selectedGroup = null;
        $periods = [];
        $calculationResult = null;
        $settlementHistory = [];
        $currentMembership = null;

        if (!empty($selectedTripId)) {
            // 2. Verifikasi akses
            $currentMembership = $this->checkTripAccess((int)$selectedTripId);
            if (!$currentMembership) {
                return redirect()->to('backend/settlements')->with('error', 'Anda tidak memiliki akses ke trip ini.');
            }

            $selectedTrip = $this->tripModel->find($selectedTripId);
            
            // 3. Ambil list periode
            $periods = $this->periodModel->where('trip_id', $selectedTripId)->orderBy('created_at', 'ASC')->findAll();

            // Jika ada periode terpilih, jalankan engine kalkulasi
            if (!empty($selectedPeriodId)) {
                $engine = new \App\Libraries\CalculationEngine();
                try {
                    $calculationResult = $engine->calculatePeriod((int)$selectedPeriodId);
                } catch (\Exception $e) {
                    // Fail silently
                }

                // 4. Ambil riwayat transfer di periode ini
                $settlementHistory = $this->settlementModel->select('settlements.*, COALESCE(NULLIF(sender.fullname, \'\'), sender.username) as sender_name, COALESCE(NULLIF(receiver.fullname, \'\'), receiver.username) as receiver_name')
                                                           ->join('users sender', 'sender.id = settlements.from_user_id')
                                                           ->join('users receiver', 'receiver.id = settlements.to_user_id')
                                                           ->where('settlements.period_id', $selectedPeriodId)
                                                           ->orderBy('settlements.created_at', 'DESC')
                                                           ->findAll();
            }

            $session->set('set_last_trip_id',   $selectedTripId);
            $session->set('set_last_period_id', $selectedPeriodId ?: null);
            $session->set('set_last_group_id',  null);
        } else if (!empty($selectedGroupId)) {
            // Verifikasi akses user ke group terpilih
            $currentMembership = $this->groupMemberModel->where('group_id', $selectedGroupId)
                                                         ->where('user_id', $userId)
                                                         ->first();
            if (!$currentMembership) {
                return redirect()->to('backend/settlements')->with('error', 'Anda tidak memiliki akses ke grup ini.');
            }

            $selectedGroup = $this->groupMemberModel->select('groups.*')
                                                   ->join('groups', 'groups.id = group_members.group_id')
                                                   ->where('groups.id', $selectedGroupId)
                                                   ->first();

            $session->set('set_last_trip_id',   null);
            $session->set('set_last_period_id', null);
            $session->set('set_last_group_id',  $selectedGroupId);
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
            'pageTitle'         => 'Settlement Saldo',
            'availableTrips'    => $availableTrips,
            'selectedTripId'    => $selectedTripId,
            'selectedTrip'      => $selectedTrip,
            'selectedGroupId'   => $selectedGroupId ?? null,
            'selectedGroup'     => $selectedGroup ?? null,
            'selectedPeriodId'  => $selectedPeriodId,
            'periods'           => $periods,
            'calculationResult' => $calculationResult,
            'settlementHistory' => $settlementHistory,
            'currentMembership' => $currentMembership,
            'allPeriodsJson'    => json_encode($allPeriodsJson),
            'user'              => user(),
            'filterHierarchy'   => $filterHierarchy,
        ];

        return view('backend/settlements/index', $data);
    }

    /**
     * Konfirmasi / bayar transfer settlement
     */
    public function pay()
    {
        $rules = [
            'trip_id'      => 'required|numeric',
            'period_id'    => 'required|numeric',
            'from_user_id' => 'required|numeric',
            'to_user_id'   => 'required|numeric',
            'amount'       => 'required|numeric|greater_than[0]',
            'note'         => 'permit_empty|max_length[255]',
            'proof_image'  => 'permit_empty|is_image[proof_image]|max_size[proof_image,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tripId = (int)$this->request->getPost('trip_id');
        $periodId = (int)$this->request->getPost('period_id');
        $fromUserId = (int)$this->request->getPost('from_user_id');
        $toUserId = (int)$this->request->getPost('to_user_id');
        $amount = (int)$this->request->getPost('amount');

        // Verifikasi akses
        $membership = $this->checkTripAccess($tripId);
        if (!$membership) {
            return redirect()->back()->withInput()->with('error', 'Anda tidak memiliki akses ke trip ini.');
        }

        // Pastikan yang mencatat transfer adalah si debitur pengirim
        if ($fromUserId !== (int)user_id() && $membership['role'] !== 'admin') {
            return redirect()->back()->withInput()->with('error', 'Anda hanya dapat mengkonfirmasi transfer yang dikirim dari akun Anda sendiri.');
        }

        // Handle upload file
        $proofPath = null;
        $img = $this->request->getFile('proof_image');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $proofPath = $this->compressAndSaveImage($img, 'uploads/settlements');
        }

        // Simpan data settlement
        $this->settlementModel->insert([
            'trip_id'      => $tripId,
            'period_id'    => $periodId,
            'from_user_id' => $fromUserId,
            'to_user_id'   => $toUserId,
            'amount'       => $amount,
            'status'       => 'pending',
            'proof_image'  => $proofPath,
            'note'         => $this->request->getPost('note') ?: null,
        ]);

        return redirect()->to('backend/settlements?trip_id=' . $tripId . '&period_id=' . $periodId)->with('success', 'Bukti transfer berhasil diunggah. Menunggu verifikasi admin.');
    }

    /**
     * Approve / Tandai lunas transfer settlement (Admin Only)
     */
    public function approve(int $settlementId)
    {
        $settlement = $this->settlementModel->find($settlementId);
        if (!$settlement) {
            return redirect()->back()->with('error', 'Data transfer tidak ditemukan.');
        }

        // Verifikasi akses trip
        $membership = $this->checkTripAccess((int)$settlement['trip_id']);
        if (!$membership) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke trip ini.');
        }

        // Hanya penerima (kreditur/to_user_id) ATAU admin yang bisa konfirmasi
        $isReceiver = ((int)user_id() === (int)$settlement['to_user_id']);
        if (!$isReceiver && $membership['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Hanya penerima transfer atau admin yang dapat mengkonfirmasi penerimaan ini.');
        }

        // Update status menjadi paid
        $this->settlementModel->update($settlementId, [
            'status'  => 'paid',
            'paid_at' => date('Y-m-d H:i:s')
        ]);

        $successMsg = 'Transfer berhasil diverifikasi dan ditandai lunas.';

        // Opsi: Tutup buku periode sekalian (jika dipilih oleh pengguna)
        $lockPeriod = $this->request->getGet('lock_period');
        if ($lockPeriod == '1' && !empty($settlement['period_id'])) {
            $period = $this->periodModel->find($settlement['period_id']);
            if ($period && ($period['status'] ?? 'open') === 'open') {
                $this->periodModel->update($settlement['period_id'], ['status' => 'settled']);
                $successMsg .= ' Periode "' . esc($period['label']) . '" juga telah ditutup (Settled).';
            }
        }

        return redirect()->to('backend/settlements?trip_id=' . $settlement['trip_id'] . '&period_id=' . $settlement['period_id'])->with('success', $successMsg);
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
