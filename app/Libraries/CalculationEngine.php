<?php

namespace App\Libraries;

use App\Models\PeriodModel;
use App\Models\PeriodActiveMemberModel;
use App\Models\TransactionModel;
use App\Models\AdjustmentModel;
use App\Models\GroupMemberModel;
use Myth\Auth\Models\UserModel;

class CalculationEngine
{
    protected $periodModel;
    protected $activeMemberModel;
    protected $transactionModel;
    protected $adjustmentModel;
    protected $groupMemberModel;
    protected $userModel;

    public function __construct()
    {
        $this->periodModel = new PeriodModel();
        $this->activeMemberModel = new PeriodActiveMemberModel();
        $this->transactionModel = new TransactionModel();
        $this->adjustmentModel = new AdjustmentModel();
        $this->groupMemberModel = new GroupMemberModel();
        $this->userModel = new UserModel();
    }

    /**
     * Menghitung rekapitulasi pengeluaran dan settlement rekomendasi untuk satu periode.
     */
    public function calculatePeriod(int $periodId): array
    {
        // 1. Ambil data periode
        $period = $this->periodModel->find($periodId);
        if (!$period) {
            throw new \InvalidArgumentException("Periode dengan ID {$periodId} tidak ditemukan.");
        }

        // 2. Ambil anggota aktif untuk periode ini
        $activeMembers = $this->activeMemberModel->select('period_active_members.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as username, users.email')
                                                 ->join('users', 'users.id = period_active_members.user_id')
                                                 ->where('period_id', $periodId)
                                                 ->findAll();
        
        $activeMemberIds = array_map('intval', array_column($activeMembers, 'user_id'));

        // 3. Ambil seluruh transaksi pada periode ini
        $transactions = $this->transactionModel->select('transactions.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as paid_by_name')
                                               ->join('users', 'users.id = transactions.paid_by')
                                               ->where('period_id', $periodId)
                                               ->orderBy('date', 'ASC')
                                               ->findAll();

        // 4. Ambil seluruh adjustment/distribusi kustom untuk transaksi periode ini
        $transactionIds = array_column($transactions, 'id');
        $adjustments = [];
        if (!empty($transactionIds)) {
            $adjustments = $this->adjustmentModel->select('transaction_adjustments.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as username')
                                                 ->join('users', 'users.id = transaction_adjustments.target_user_id')
                                                 ->whereIn('transaction_id', $transactionIds)
                                                 ->findAll();
        }

        // Kelompokkan adjustments berdasarkan transaction_id
        $adjustmentsByTrans = [];
        foreach ($adjustments as $adj) {
            $adjustmentsByTrans[$adj['transaction_id']][] = $adj;
        }

        // 5. Kumpulkan semua user_id yang terlibat
        // Terdiri dari: anggota aktif periode + payer + target adjustment
        $involvedUserIds = $activeMemberIds;
        foreach ($transactions as $t) {
            $involvedUserIds[] = (int)$t['paid_by'];
            if (isset($adjustmentsByTrans[$t['id']])) {
                foreach ($adjustmentsByTrans[$t['id']] as $adj) {
                    $involvedUserIds[] = (int)$adj['target_user_id'];
                }
            }
        }
        $involvedUserIds = array_values(array_unique(array_map('intval', $involvedUserIds)));

        // Ambil nama user untuk semua yang terlibat
        $involvedUsers = [];
        if (!empty($involvedUserIds)) {
            $usersList = $this->userModel->whereIn('id', $involvedUserIds)->findAll();
            foreach ($usersList as $u) {
                $uid = is_object($u) ? $u->id : $u['id'];
                $uname = is_object($u) ? $u->username : $u['username'];
                $fullname = is_object($u) ? ($u->fullname ?? '') : ($u['fullname'] ?? '');
                $displayName = !empty($fullname) ? $fullname : $uname;
                $involvedUsers[(int)$uid] = $displayName;
            }
        }

        // Inisialisasi balances
        $balances = [];
        foreach ($involvedUserIds as $uid) {
            $balances[$uid] = [
                'user_id'           => $uid,
                'username'          => $involvedUsers[$uid] ?? 'Unknown',
                'is_active_member'  => in_array($uid, $activeMemberIds),
                'total_paid'        => 0, // nominal yang dibayarkan dari dompet
                'shared_share'      => 0, // bagian dari pembagian rata
                'individual_charge' => 0, // bagian dari pembagian kustom
                'net_balance'       => 0, // saldo akhir: total_paid - shared_share - individual_charge
            ];
        }

        $totalShared = 0;
        $totalIndividual = 0;
        $totalTransactions = 0;

        // 6. Hitung distribusi masing-masing transaksi
        $numActiveMembers = count($activeMemberIds);

        foreach ($transactions as $t) {
            $payerId = (int)$t['paid_by'];
            $amount = (int)$t['amount'];
            $totalTransactions += $amount;

            // Catat pengeluaran payer
            if (isset($balances[$payerId])) {
                $balances[$payerId]['total_paid'] += $amount;
            }

            if ($t['type'] === 'shared') {
                $totalShared += $amount;
                
                // Bagi rata ke anggota aktif periode
                if ($numActiveMembers > 0) {
                    $share = (int)round($amount / $numActiveMembers);
                    foreach ($activeMemberIds as $amId) {
                        if (isset($balances[$amId])) {
                            $balances[$amId]['shared_share'] += $share;
                        }
                    }
                }
            } elseif ($t['type'] === 'individual') {
                $totalIndividual += $amount;

                // Bebankan ke target individual
                $transAdjs = $adjustmentsByTrans[$t['id']] ?? [];
                foreach ($transAdjs as $adj) {
                    $targetUid = (int)$adj['target_user_id'];
                    $adjAmount = (int)$adj['amount'];
                    if (isset($balances[$targetUid])) {
                        $balances[$targetUid]['individual_charge'] += $adjAmount;
                    }
                }
            }
        }

        // 7. Hitung saldo bersih (net balance)
        foreach ($balances as $uid => &$b) {
            $b['net_balance'] = $b['total_paid'] - $b['shared_share'] - $b['individual_charge'];
        }
        unset($b);

        // 8. Hitung rekomendasi transfer (Greedy Settlement Algorithm)
        $settlements = [];
        $debtors = [];  // saldo < 0 (berutang)
        $creditors = []; // saldo > 0 (piutang)

        foreach ($balances as $uid => $b) {
            if ($b['net_balance'] < 0) {
                $debtors[] = [
                    'user_id'  => $uid,
                    'username' => $b['username'],
                    'amount'   => abs($b['net_balance'])
                ];
            } elseif ($b['net_balance'] > 0) {
                $creditors[] = [
                    'user_id'  => $uid,
                    'username' => $b['username'],
                    'amount'   => $b['net_balance']
                ];
            }
        }

        // Urutkan debitur menurun (paling banyak utang dulu)
        usort($debtors, function($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        // Urutkan kreditur menurun (paling banyak piutang dulu)
        usort($creditors, function($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        $dIdx = 0;
        $cIdx = 0;
        $dCount = count($debtors);
        $cCount = count($creditors);

        while ($dIdx < $dCount && $cIdx < $cCount) {
            $debtor = &$debtors[$dIdx];
            $creditor = &$creditors[$cIdx];

            $transferAmount = min($debtor['amount'], $creditor['amount']);

            if ($transferAmount > 0) {
                $settlements[] = [
                    'from_user_id'  => $debtor['user_id'],
                    'from_username' => $debtor['username'],
                    'to_user_id'    => $creditor['user_id'],
                    'to_username'   => $creditor['username'],
                    'amount'        => $transferAmount
                ];

                $debtor['amount'] -= $transferAmount;
                $creditor['amount'] -= $transferAmount;
            }

            // Pindah index jika utang/piutang lunas (toleransi pembulatan 1 rupiah)
            if ($debtor['amount'] <= 1) {
                $dIdx++;
            }
            if ($creditor['amount'] <= 1) {
                $cIdx++;
            }
        }

        $splitRata = 0;
        if ($numActiveMembers > 0) {
            $splitRata = (int)round($totalShared / $numActiveMembers);
        }

        return [
            'period'         => $period,
            'active_members' => $activeMembers,
            'participants'   => array_values($balances),
            'summary'        => [
                'total_shared'       => $totalShared,
                'split_rata'         => $splitRata,
                'total_individual'   => $totalIndividual,
                'total_transactions' => $totalTransactions,
            ],
            'settlements'    => $settlements
        ];
    }
}
