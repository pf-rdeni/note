<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\GroupModel;
use App\Models\GroupMemberModel;
use App\Models\TripModel;
use App\Models\PeriodModel;
use App\Models\TransactionModel;
use App\Models\InstallmentModel;
use App\Models\InstallmentPaymentModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $userId = user_id();

        // 1. Dapatkan grup yang diikuti user
        $groupMemberModel = new GroupMemberModel();
        $groupMemberships = $groupMemberModel->where('user_id', $userId)->findAll();
        $groupIds = array_column($groupMemberships, 'group_id');

        $numGroups = count($groupIds);
        $numTrips = 0;
        $totalExpenses = 0;
        $recentTransactions = [];
        $spendingChartData = [];

        if ($numGroups > 0) {
            // 2. Dapatkan trips di bawah grup-grup tersebut
            $tripModel = new TripModel();
            $trips = $tripModel->whereIn('group_id', $groupIds)->findAll();
            $numTrips = count($trips);
            $tripIds = array_column($trips, 'id');

            if ($numTrips > 0) {
                // 3. Hitung total pengeluaran
                $transactionModel = new TransactionModel();
                
                // Cari jumlah nominal belanja
                $db = \Config\Database::connect();
                $sumQuery = $db->table('transactions')
                               ->selectSum('amount')
                               ->whereIn('trip_id', $tripIds)
                               ->get()
                               ->getRow();
                $totalExpenses = (int)($sumQuery->amount ?? 0);

                // Cari total pengeluaran pembagian rata (shared) & individual untuk user login
                $allTransactions = $db->table('transactions')
                                      ->select('transactions.id, transactions.amount, transactions.type, transactions.period_id, transactions.trip_id, trips.group_id, trips.name as trip_name, groups.name as group_name')
                                      ->join('trips', 'trips.id = transactions.trip_id')
                                      ->join('groups', 'groups.id = trips.group_id')
                                      ->whereIn('transactions.trip_id', $tripIds)
                                      ->get()
                                      ->getResultArray();

                $periodActiveMembers = [];
                $periodMeta = [];
                if (!empty($tripIds)) {
                    $periodsData = $db->table('trip_periods')
                                         ->select('id, label, created_at')
                                         ->whereIn('trip_id', $tripIds)
                                         ->orderBy('created_at', 'ASC')
                                         ->get()
                                         ->getResultArray();
                    $allPeriodIds = array_column($periodsData, 'id');
                    foreach ($periodsData as $p) {
                        $periodMeta[$p['id']] = [
                            'label' => $p['label'],
                            'created_at' => $p['created_at']
                        ];
                    }

                    if (!empty($allPeriodIds)) {
                        $periodActiveMembers = $db->table('period_active_members')
                                                  ->select('period_id, user_id')
                                                  ->whereIn('period_id', $allPeriodIds)
                                                  ->get()
                                                  ->getResultArray();
                    }
                }

                $activeUsersByPeriod = [];
                foreach ($periodActiveMembers as $pam) {
                    $activeUsersByPeriod[$pam['period_id']][] = (int)$pam['user_id'];
                }

                $myAdjustments = [];
                if (!empty($allTransactions)) {
                    $allTransIds = array_column($allTransactions, 'id');
                    $myAdjustments = $db->table('transaction_adjustments')
                                        ->select('transaction_id, amount')
                                        ->where('target_user_id', $userId)
                                        ->whereIn('transaction_id', $allTransIds)
                                        ->get()
                                        ->getResultArray();
                }

                $myAdjustmentsByTrans = [];
                foreach ($myAdjustments as $adj) {
                    $myAdjustmentsByTrans[$adj['transaction_id']] = (int)$adj['amount'];
                }

                $myExpenses = 0;
                $myExpensesByPeriod = [];
                $myExpensesByTrip = [];
                $myExpensesByGroup = [];
                $tripMeta = [];
                $groupMeta = [];

                foreach ($allTransactions as $t) {
                    $transId = $t['id'];
                    $amount = (int)$t['amount'];
                    $type = $t['type'];
                    $periodId = (int)$t['period_id'];
                    $tripId = (int)$t['trip_id'];
                    $groupId = (int)$t['group_id'];

                    $tripMeta[$tripId] = $t['trip_name'];
                    $groupMeta[$groupId] = $t['group_name'];

                    $transShare = 0;
                    if ($type === 'shared') {
                        $activeList = $activeUsersByPeriod[$periodId] ?? [];
                        $numActive = count($activeList);
                        if ($numActive > 0 && in_array($userId, $activeList)) {
                            $transShare = (int)round($amount / $numActive);
                        }
                    } elseif ($type === 'individual') {
                        if (isset($myAdjustmentsByTrans[$transId])) {
                            $transShare = $myAdjustmentsByTrans[$transId];
                        }
                    }

                    if ($transShare > 0) {
                        $myExpenses += $transShare;
                        $myExpensesByPeriod[$periodId] = ($myExpensesByPeriod[$periodId] ?? 0) + $transShare;
                        $myExpensesByTrip[$tripId] = ($myExpensesByTrip[$tripId] ?? 0) + $transShare;
                        $myExpensesByGroup[$groupId] = ($myExpensesByGroup[$groupId] ?? 0) + $transShare;
                    }
                }

                // 4. Ambil 5 transaksi terbaru
                $recentTransactions = $transactionModel->select('transactions.*, COALESCE(NULLIF(users.fullname, \'\'), users.username) as paid_by_name, trips.name as trip_name')
                                                       ->join('users', 'users.id = transactions.paid_by')
                                                       ->join('trips', 'trips.id = transactions.trip_id')
                                                       ->whereIn('transactions.trip_id', $tripIds)
                                                       ->orderBy('transactions.date', 'DESC')
                                                       ->orderBy('transactions.created_at', 'DESC')
                                                       ->limit(5)
                                                       ->findAll();

                // 5. Agregasi pengeluaran per periode untuk Chart.js (Total)
                $spendingQuery = $db->table('transactions')
                                    ->select('trip_periods.label as period_label, SUM(transactions.amount) as total_amount')
                                    ->join('trip_periods', 'trip_periods.id = transactions.period_id')
                                    ->whereIn('transactions.trip_id', $tripIds)
                                    ->groupBy(['transactions.period_id', 'trip_periods.label', 'trip_periods.created_at'])
                                    ->orderBy('trip_periods.created_at', 'ASC')
                                    ->get()
                                    ->getResultArray();

                foreach ($spendingQuery as $row) {
                    $spendingChartData[] = [
                        'label'  => $row['period_label'],
                        'amount' => (int)$row['total_amount']
                    ];
                }

                // 6. Agregasi beban pengeluaran saya per periode
                $avgPeriodChartData = [];
                foreach ($periodMeta as $pid => $meta) {
                    $avgPeriodChartData[] = [
                        'label'  => $meta['label'],
                        'amount' => $myExpensesByPeriod[$pid] ?? 0
                    ];
                }

                // 7. Agregasi beban pengeluaran saya per kegiatan
                $avgTripChartData = [];
                foreach ($myExpensesByTrip as $tripId => $amount) {
                    $avgTripChartData[] = [
                        'label'  => $tripMeta[$tripId] ?? 'Unknown Trip',
                        'amount' => $amount
                    ];
                }

                // 7.5. Agregasi beban pengeluaran saya per kelompok (grup)
                $avgGroupChartData = [];
                foreach ($myExpensesByGroup as $groupId => $amount) {
                    $avgGroupChartData[] = [
                        'label'  => $groupMeta[$groupId] ?? 'Unknown Group',
                        'amount' => $amount
                    ];
                }

                // 8. Agregasi total kontribusi pembayaran per anggota keluarga
                $memberSpendingQuery = $db->table('transactions')
                                          ->select('COALESCE(NULLIF(users.fullname, \'\'), users.username) as username, SUM(transactions.amount) as total_amount')
                                          ->join('users', 'users.id = transactions.paid_by')
                                          ->whereIn('transactions.trip_id', $tripIds)
                                          ->groupBy(['transactions.paid_by', 'COALESCE(NULLIF(users.fullname, \'\'), users.username)'])
                                          ->orderBy('total_amount', 'DESC')
                                          ->get()
                                          ->getResultArray();

                $memberSpendingChartData = [];
                foreach ($memberSpendingQuery as $row) {
                    $memberSpendingChartData[] = [
                        'label'  => $row['username'],
                        'amount' => (int)$row['total_amount']
                    ];
                }

                // 9. Ambil semua transaksi per periode untuk tren/perbandingan item pengeluaran
                $transactionTrendsQuery = $db->table('transactions')
                                             ->select('transactions.id, transactions.description, transactions.amount, transactions.period_id, transactions.paid_by, COALESCE(NULLIF(users.fullname, \'\'), users.username) as paid_by_name, trip_periods.label as period_label, trip_periods.trip_id, trips.name as trip_name, trips.group_id, groups.name as group_name')
                                             ->join('users', 'users.id = transactions.paid_by')
                                             ->join('trip_periods', 'trip_periods.id = transactions.period_id')
                                             ->join('trips', 'trips.id = trip_periods.trip_id')
                                             ->join('groups', 'groups.id = trips.group_id')
                                             ->whereIn('transactions.trip_id', $tripIds)
                                             ->orderBy('transactions.amount', 'DESC')
                                             ->get()
                                             ->getResultArray();

                $trendHierarchy = [];
                $trendPeriods = [];
                $trendTransactionsByPeriod = [];
                foreach ($transactionTrendsQuery as $row) {
                    $groupId = (int)$row['group_id'];
                    $groupName = $row['group_name'];
                    $tripId = (int)$row['trip_id'];
                    $tripName = $row['trip_name'];
                    $periodId = (int)$row['period_id'];
                    $periodLabel = $row['period_label'];

                    if (!isset($trendHierarchy[$groupId])) {
                        $trendHierarchy[$groupId] = [
                            'name' => $groupName,
                            'trips' => []
                        ];
                    }
                    if (!isset($trendHierarchy[$groupId]['trips'][$tripId])) {
                        $trendHierarchy[$groupId]['trips'][$tripId] = [
                            'name' => $tripName,
                            'periods' => []
                        ];
                    }
                    if (!isset($trendHierarchy[$groupId]['trips'][$tripId]['periods'][$periodId])) {
                        $trendHierarchy[$groupId]['trips'][$tripId]['periods'][$periodId] = $periodLabel;
                    }

                    if (!isset($trendPeriods[$periodId])) {
                        $trendPeriods[$periodId] = $periodLabel;
                    }

                    $trendTransactionsByPeriod[$periodId][] = [
                        'description'  => $row['description'],
                        'amount'       => (int)$row['amount'],
                        'paid_by_name' => $row['paid_by_name'],
                        'paid_by'      => (int)$row['paid_by']
                    ];
                }
            }
        }

        // --- 4. Hitung Statistik & Proyeksi Cicilan (Global) ---
        $installmentModel = new InstallmentModel();
        $paymentModel     = new InstallmentPaymentModel();
        $userIdInt        = (int)$userId;

        $userInstallments = $installmentModel->select('installments.*, 
                COALESCE(NULLIF(lender.fullname, \'\'), lender.username) as lender_name,
                COALESCE(NULLIF(borrower.fullname, \'\'), borrower.username) as borrower_name,
                trips.name as trip_name')
            ->join('users lender', 'lender.id = installments.lender_user_id', 'left')
            ->join('users borrower', 'borrower.id = installments.borrower_user_id')
            ->join('trips', 'trips.id = installments.trip_id')
            ->groupStart()
                ->where('installments.borrower_user_id', $userIdInt)
                ->orWhere('installments.lender_user_id', $userIdInt)
            ->groupEnd()
            ->findAll();

        $userInstallmentIds = array_column($userInstallments, 'id');
        $userPayments = [];
        if (!empty($userInstallmentIds)) {
            $userPayments = $paymentModel->whereIn('installment_id', $userInstallmentIds)
                ->orderBy('due_date', 'ASC')
                ->findAll();
        }

        $paymentsByInstallment = [];
        foreach ($userPayments as $p) {
            $paymentsByInstallment[$p['installment_id']][] = $p;
        }

        $currentMonth = date('Y-m-01');
        $next6Months = [];
        $monthKeys = [];
        for ($i = 0; $i < 6; $i++) {
            $mKey = date('Y-m-01', strtotime("+$i month"));
            $mLabel = date('M Y', strtotime("+$i month"));
            $monthKeys[] = $mKey;
            $next6Months[$mKey] = [
                'label'    => $mLabel,
                'due_pay'  => 0, // Tagihan saya (Hutang)
                'due_rcv'  => 0  // Piutang saya (Dana Masuk)
            ];
        }

        $dashboardSisaPinjaman = 0;
        $dashboardSisaPiutang = 0;
        $dashboardTagihanBulanIni = 0;
        $dashboardPiutangBulanIni = 0;

        $borrowerInstallmentTrends = [];
        $lenderInstallmentTrends = [];

        // Colors palette for chart datasets
        $colorPalette = [
            'rgba(0, 123, 255, 0.75)',  // Blue
            'rgba(40, 167, 69, 0.75)',   // Green
            'rgba(253, 126, 20, 0.75)',  // Orange
            'rgba(23, 162, 184, 0.75)',  // Teal
            'rgba(111, 66, 193, 0.75)',  // Purple
            'rgba(220, 53, 69, 0.75)',   // Red
            'rgba(255, 193, 7, 0.75)',   // Yellow
            'rgba(74, 85, 104, 0.75)',   // Gray
            'rgba(236, 72, 153, 0.75)',  // Pink
            'rgba(20, 184, 166, 0.75)'   // Cyan
        ];

        $cIndexB = 0;
        $cIndexL = 0;

        foreach ($userInstallments as $inst) {
            $instId = $inst['id'];
            $isBorrower = ((int)$inst['borrower_user_id'] === $userIdInt);
            $isLender   = ((int)$inst['lender_user_id'] === $userIdInt);
            
            $instPayments = $paymentsByInstallment[$instId] ?? [];
            
            $monthlyValues = array_fill_keys($monthKeys, 0);
            $hasUnpaidPayments = false;

            foreach ($instPayments as $p) {
                $dueAmt = (int)$p['due_amount'];
                if ($p['status'] !== 'paid') {
                    if ($isBorrower) {
                        $dashboardSisaPinjaman += $dueAmt;
                        if ($p['due_date'] === $currentMonth) {
                            $dashboardTagihanBulanIni += $dueAmt;
                        }
                    }
                    if ($isLender) {
                        $dashboardSisaPiutang += $dueAmt;
                        if ($p['due_date'] === $currentMonth) {
                            $dashboardPiutangBulanIni += $dueAmt;
                        }
                    }

                    // Add to trends monthly values
                    if (isset($monthlyValues[$p['due_date']])) {
                        $monthlyValues[$p['due_date']] = $dueAmt;
                        $hasUnpaidPayments = true;
                    }
                }

                // Monthly projection chart data (next 6 months)
                if (isset($next6Months[$p['due_date']])) {
                    if ($p['status'] !== 'paid') {
                        if ($isBorrower) {
                            $next6Months[$p['due_date']]['due_pay'] += $dueAmt;
                        }
                        if ($isLender) {
                            $next6Months[$p['due_date']]['due_rcv'] += $dueAmt;
                        }
                    }
                }
            }

            // Generate trends dataset
            if ($hasUnpaidPayments) {
                if ($isBorrower) {
                    $color = $colorPalette[$cIndexB % count($colorPalette)];
                    $borrowerInstallmentTrends[] = [
                        'label'           => $inst['description'],
                        'data'            => array_values($monthlyValues),
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                        'borderWidth'     => 1
                    ];
                    $cIndexB++;
                }
                if ($isLender) {
                    $color = $colorPalette[$cIndexL % count($colorPalette)];
                    $counterpart = $inst['borrower_name'] ?? 'Anggota';
                    $lenderInstallmentTrends[] = [
                        'label'           => $inst['description'] . ' (' . $counterpart . ')',
                        'data'            => array_values($monthlyValues),
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                        'borderWidth'     => 1
                    ];
                    $cIndexL++;
                }
            }
        }

        $data = [
            'pageTitle'                 => 'Dashboard',
            'user'                      => user(),
            'numGroups'                 => $numGroups,
            'numTrips'                  => $numTrips,
            'totalExpenses'             => $totalExpenses,
            'myExpenses'                => $myExpenses ?? 0,
            'recentTransactions'        => $recentTransactions,
            'spendingChartData'         => $spendingChartData,
            'avgPeriodChartData'        => $avgPeriodChartData ?? [],
            'avgTripChartData'          => $avgTripChartData ?? [],
            'avgGroupChartData'         => $avgGroupChartData ?? [],
            'memberSpendingChartData'   => $memberSpendingChartData ?? [],
            'trendPeriods'              => $trendPeriods ?? [],
            'trendTransactionsByPeriod' => $trendTransactionsByPeriod ?? [],
            'trendHierarchy'            => $trendHierarchy ?? [],
            'installmentStats'          => [
                'sisa_pinjaman'     => $dashboardSisaPinjaman,
                'sisa_piutang'      => $dashboardSisaPiutang,
                'tagihan_bulan_ini' => $dashboardTagihanBulanIni,
                'piutang_bulan_ini' => $dashboardPiutangBulanIni,
                'chart_labels'      => array_column($next6Months, 'label'),
                'chart_pay'         => array_column($next6Months, 'due_pay'),
                'chart_rcv'         => array_column($next6Months, 'due_rcv'),
                'borrower_trends'   => $borrowerInstallmentTrends,
                'lender_trends'     => $lenderInstallmentTrends,
                'has_installments'  => !empty($userInstallments)
            ]
        ];

        return view('backend/dashboard/index', $data);
    }
}
