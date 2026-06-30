<?php

namespace App\Models;

use CodeIgniter\Model;

class InstallmentGroupPaymentModel extends Model
{
    protected $table            = 'installment_group_payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'trip_id',
        'lender_user_id',
        'borrower_user_id',
        'source_type',
        'due_month',
        'total_due',
        'total_paid',
        'status',
        'paid_at',
        'proof_image',
        'note',
        'created_by',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'trip_id'          => 'required|numeric',
        'borrower_user_id' => 'required|numeric',
        'source_type'      => 'required|in_list[member_loan,credit_card]',
        'due_month'        => 'required|valid_date[Y-m-d]',
        'total_due'        => 'required|numeric|greater_than[0]',
        'status'           => 'permit_empty|in_list[paid,partial]',
        'created_by'       => 'required|numeric',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Cek apakah bulan tertentu sudah dibayar untuk lender-borrower pair
     */
    public function isPaid(int $borrowerUserId, ?int $lenderUserId, string $dueMonth, string $sourceType): bool
    {
        $query = $this->where('borrower_user_id', $borrowerUserId)
            ->where('due_month', $dueMonth)
            ->where('source_type', $sourceType);

        if ($lenderUserId !== null) {
            $query->where('lender_user_id', $lenderUserId);
        } else {
            $query->where('lender_user_id IS NULL', null, false);
        }

        return $query->countAllResults() > 0;
    }

    /**
     * Ambil riwayat pembayaran per trip dengan nama pengirim & penerima
     */
    public function getHistoryByTrip(int $tripId, int $userId): array
    {
        return $this->select('installment_group_payments.*,
                COALESCE(NULLIF(lender.fullname, \'\'), lender.username) as lender_name,
                COALESCE(NULLIF(borrower.fullname, \'\'), borrower.username) as borrower_name')
            ->join('users lender', 'lender.id = installment_group_payments.lender_user_id', 'left')
            ->join('users borrower', 'borrower.id = installment_group_payments.borrower_user_id')
            ->where('installment_group_payments.trip_id', $tripId)
            ->groupStart()
                ->where('installment_group_payments.borrower_user_id', $userId)
                ->orWhere('installment_group_payments.lender_user_id', $userId)
            ->groupEnd()
            ->orderBy('installment_group_payments.due_month', 'DESC')
            ->findAll();
    }

    /**
     * Ambil riwayat pembayaran per trip di seluruh trip yang ditentukan
     */
    public function getHistoryAllTrips(array $tripIds, int $userId): array
    {
        if (empty($tripIds)) return [];
        return $this->select('installment_group_payments.*,
                COALESCE(NULLIF(lender.fullname, \'\'), lender.username) as lender_name,
                COALESCE(NULLIF(borrower.fullname, \'\'), borrower.username) as borrower_name')
            ->join('users lender', 'lender.id = installment_group_payments.lender_user_id', 'left')
            ->join('users borrower', 'borrower.id = installment_group_payments.borrower_user_id')
            ->whereIn('installment_group_payments.trip_id', $tripIds)
            ->groupStart()
                ->where('installment_group_payments.borrower_user_id', $userId)
                ->orWhere('installment_group_payments.lender_user_id', $userId)
            ->groupEnd()
            ->orderBy('installment_group_payments.due_month', 'DESC')
            ->findAll();
    }
}
