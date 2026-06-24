<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\GroupModel;
use App\Models\GroupMemberModel;
use App\Models\TripModel;
use App\Models\PeriodModel;
use App\Models\TransactionModel;

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

                // 4. Ambil 5 transaksi terbaru
                $recentTransactions = $transactionModel->select('transactions.*, users.username as paid_by_name, trips.name as trip_name')
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

                // 6. Agregasi pengeluaran rata-rata per orang per periode
                $periodAvgQuery = $db->table('transactions')
                                     ->select('trip_periods.label as period_label, SUM(transactions.amount) as total_amount, (SELECT COUNT(*) FROM group_members WHERE group_members.group_id = trips.group_id) as member_count')
                                     ->join('trip_periods', 'trip_periods.id = transactions.period_id')
                                     ->join('trips', 'trips.id = transactions.trip_id')
                                     ->whereIn('transactions.trip_id', $tripIds)
                                     ->groupBy(['transactions.period_id', 'trip_periods.label', 'trips.group_id', 'trip_periods.created_at'])
                                     ->orderBy('trip_periods.created_at', 'ASC')
                                     ->get()
                                     ->getResultArray();

                $avgPeriodChartData = [];
                foreach ($periodAvgQuery as $row) {
                    $memberCount = (int)($row['member_count'] ?? 1);
                    $memberCount = $memberCount > 0 ? $memberCount : 1;
                    $avgPeriodChartData[] = [
                        'label'  => $row['period_label'],
                        'amount' => (int)($row['total_amount'] / $memberCount)
                    ];
                }

                // 7. Agregasi pengeluaran rata-rata per orang per kegiatan
                $tripAvgQuery = $db->table('transactions')
                                   ->select('trips.name as trip_name, SUM(transactions.amount) as total_amount, (SELECT COUNT(*) FROM group_members WHERE group_members.group_id = trips.group_id) as member_count')
                                   ->join('trips', 'trips.id = transactions.trip_id')
                                   ->whereIn('transactions.trip_id', $tripIds)
                                   ->groupBy(['transactions.trip_id', 'trips.name', 'trips.group_id'])
                                   ->get()
                                   ->getResultArray();

                $avgTripChartData = [];
                foreach ($tripAvgQuery as $row) {
                    $memberCount = (int)($row['member_count'] ?? 1);
                    $memberCount = $memberCount > 0 ? $memberCount : 1;
                    $avgTripChartData[] = [
                        'label'  => $row['trip_name'],
                        'amount' => (int)($row['total_amount'] / $memberCount)
                    ];
                }

                // 7.5. Agregasi pengeluaran rata-rata per orang per kelompok (grup)
                $groupAvgQuery = $db->table('transactions')
                                    ->select('groups.name as group_name, SUM(transactions.amount) as total_amount, (SELECT COUNT(*) FROM group_members WHERE group_members.group_id = groups.id) as member_count')
                                    ->join('trips', 'trips.id = transactions.trip_id')
                                    ->join('groups', 'groups.id = trips.group_id')
                                    ->whereIn('transactions.trip_id', $tripIds)
                                    ->groupBy(['trips.group_id', 'groups.name', 'groups.id'])
                                    ->get()
                                    ->getResultArray();

                $avgGroupChartData = [];
                foreach ($groupAvgQuery as $row) {
                    $memberCount = (int)($row['member_count'] ?? 1);
                    $memberCount = $memberCount > 0 ? $memberCount : 1;
                    $avgGroupChartData[] = [
                        'label'  => $row['group_name'],
                        'amount' => (int)($row['total_amount'] / $memberCount)
                    ];
                }

                // 8. Agregasi total kontribusi pembayaran per anggota keluarga
                $memberSpendingQuery = $db->table('transactions')
                                          ->select('users.username, SUM(transactions.amount) as total_amount')
                                          ->join('users', 'users.id = transactions.paid_by')
                                          ->whereIn('transactions.trip_id', $tripIds)
                                          ->groupBy(['transactions.paid_by', 'users.username'])
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
                                             ->select('transactions.id, transactions.description, transactions.amount, transactions.period_id, transactions.paid_by, users.username as paid_by_name, trip_periods.label as period_label')
                                             ->join('users', 'users.id = transactions.paid_by')
                                             ->join('trip_periods', 'trip_periods.id = transactions.period_id')
                                             ->whereIn('transactions.trip_id', $tripIds)
                                             ->orderBy('transactions.amount', 'DESC')
                                             ->get()
                                             ->getResultArray();

                $trendPeriods = [];
                $trendTransactionsByPeriod = [];
                foreach ($transactionTrendsQuery as $row) {
                    $pid = $row['period_id'];
                    if (!isset($trendPeriods[$pid])) {
                        $trendPeriods[$pid] = $row['period_label'];
                    }
                    $trendTransactionsByPeriod[$pid][] = [
                        'description'  => $row['description'],
                        'amount'       => (int)$row['amount'],
                        'paid_by_name' => $row['paid_by_name'],
                        'paid_by'      => (int)$row['paid_by']
                    ];
                }
            }
        }

        $data = [
            'pageTitle'                 => 'Dashboard',
            'user'                      => user(),
            'numGroups'                 => $numGroups,
            'numTrips'                  => $numTrips,
            'totalExpenses'             => $totalExpenses,
            'recentTransactions'        => $recentTransactions,
            'spendingChartData'         => $spendingChartData,
            'avgPeriodChartData'        => $avgPeriodChartData ?? [],
            'avgTripChartData'          => $avgTripChartData ?? [],
            'avgGroupChartData'         => $avgGroupChartData ?? [],
            'memberSpendingChartData'   => $memberSpendingChartData ?? [],
            'trendPeriods'              => $trendPeriods ?? [],
            'trendTransactionsByPeriod' => $trendTransactionsByPeriod ?? [],
        ];

        return view('backend/dashboard/index', $data);
    }
}
