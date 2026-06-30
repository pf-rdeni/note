<?php

namespace App\Models;

use CodeIgniter\Model;

class InstallmentModel extends Model
{
    protected $table            = 'installments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'trip_id',
        'description',
        'source_type',
        'lender_user_id',
        'borrower_user_id',
        'total_amount',
        'start_date',
        'installment_months',
        'monthly_amount',
        'note',
        'status',
        'created_by',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'trip_id'            => 'required|numeric',
        'description'        => 'required|min_length[3]|max_length[255]',
        'source_type'        => 'required|in_list[member_loan,credit_card]',
        'lender_user_id'     => 'permit_empty|numeric',
        'borrower_user_id'   => 'required|numeric',
        'total_amount'       => 'required|numeric|greater_than[0]',
        'start_date'         => 'required|valid_date[Y-m-d]',
        'installment_months' => 'required|numeric|greater_than[0]',
        'monthly_amount'     => 'required|numeric|greater_than[0]',
        'status'             => 'permit_empty|in_list[active,completed,cancelled]',
        'created_by'         => 'required|numeric',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Ambil installments yang bisa dilihat oleh user:
     * - borrower_user_id = $userId, ATAU
     * - lender_user_id   = $userId
     */
    public function getVisibleByUser(int $userId, int $tripId): array
    {
        return $this->select('installments.*, 
                COALESCE(NULLIF(lender.fullname, \'\'), lender.username) as lender_name,
                COALESCE(NULLIF(borrower.fullname, \'\'), borrower.username) as borrower_name')
            ->join('users lender', 'lender.id = installments.lender_user_id', 'left')
            ->join('users borrower', 'borrower.id = installments.borrower_user_id')
            ->where('installments.trip_id', $tripId)
            ->groupStart()
                ->where('installments.borrower_user_id', $userId)
                ->orWhere('installments.lender_user_id', $userId)
            ->groupEnd()
            ->orderBy('installments.source_type', 'ASC')
            ->orderBy('installments.start_date', 'ASC')
            ->findAll();
    }

    /**
     * Ambil installments yang bisa dilihat oleh user di seluruh trip yang ditentukan
     */
    public function getVisibleByUserAllTrips(int $userId, array $tripIds): array
    {
        if (empty($tripIds)) return [];

        return $this->select('installments.*, 
                COALESCE(NULLIF(lender.fullname, \'\'), lender.username) as lender_name,
                COALESCE(NULLIF(borrower.fullname, \'\'), borrower.username) as borrower_name,
                trips.name as trip_name')
            ->join('users lender', 'lender.id = installments.lender_user_id', 'left')
            ->join('users borrower', 'borrower.id = installments.borrower_user_id')
            ->join('trips', 'trips.id = installments.trip_id')
            ->whereIn('installments.trip_id', $tripIds)
            ->groupStart()
                ->where('installments.borrower_user_id', $userId)
                ->orWhere('installments.lender_user_id', $userId)
            ->groupEnd()
            ->orderBy('installments.start_date', 'ASC')
            ->findAll();
    }
}
