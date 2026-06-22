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

                // 5. Agregasi pengeluaran per periode untuk Chart.js
                $spendingQuery = $db->table('transactions')
                                    ->select('trip_periods.label as period_label, SUM(transactions.amount) as total_amount')
                                    ->join('trip_periods', 'trip_periods.id = transactions.period_id')
                                    ->whereIn('transactions.trip_id', $tripIds)
                                    ->groupBy('transactions.period_id')
                                    ->orderBy('trip_periods.created_at', 'ASC')
                                    ->get()
                                    ->getResultArray();

                foreach ($spendingQuery as $row) {
                    $spendingChartData[] = [
                        'label'  => $row['period_label'],
                        'amount' => (int)$row['total_amount']
                    ];
                }
            }
        }

        $data = [
            'pageTitle'          => 'Dashboard',
            'user'               => user(),
            'numGroups'          => $numGroups,
            'numTrips'           => $numTrips,
            'totalExpenses'      => $totalExpenses,
            'recentTransactions' => $recentTransactions,
            'spendingChartData'  => $spendingChartData
        ];

        return view('backend/dashboard/index', $data);
    }
}
