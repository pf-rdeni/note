<?php

namespace App\Models;

use CodeIgniter\Model;

class InstallmentPaymentModel extends Model
{
    protected $table            = 'installment_payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'installment_id',
        'due_date',
        'due_amount',
        'paid_amount',
        'status',
        'paid_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'installment_id' => 'required|numeric',
        'due_date'       => 'required|valid_date[Y-m-d]',
        'due_amount'     => 'required|numeric|greater_than[0]',
        'status'         => 'permit_empty|in_list[unpaid,paid,partial]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Auto-generate schedule records untuk sebuah installment
     * Dipanggil setelah installment berhasil disimpan
     */
    public function generateSchedule(int $installmentId, string $startDate, int $months, int $monthlyAmount): void
    {
        $date = new \DateTime($startDate);
        // Pastikan mulai dari hari pertama bulan
        $date->modify('first day of this month');

        for ($i = 0; $i < $months; $i++) {
            $this->insert([
                'installment_id' => $installmentId,
                'due_date'       => $date->format('Y-m-d'),
                'due_amount'     => $monthlyAmount,
                'paid_amount'    => 0,
                'status'         => 'unpaid',
            ]);
            $date->modify('+1 month');
        }
    }

    /**
     * Ambil semua jadwal per installment, diurutkan by due_date
     */
    public function getByInstallment(int $installmentId): array
    {
        return $this->where('installment_id', $installmentId)
            ->orderBy('due_date', 'ASC')
            ->findAll();
    }

    /**
     * Ambil schedule yang jatuh tempo di bulan tertentu untuk list installment IDs
     */
    public function getByInstallmentIdsAndMonth(array $installmentIds, string $dueMonth): array
    {
        if (empty($installmentIds)) return [];

        return $this->whereIn('installment_id', $installmentIds)
            ->where('due_date', $dueMonth)
            ->findAll();
    }

    /**
     * Cek apakah semua jadwal installment sudah lunas
     */
    public function allPaid(int $installmentId): bool
    {
        $unpaid = $this->where('installment_id', $installmentId)
            ->where('status !=', 'paid')
            ->countAllResults();

        return $unpaid === 0;
    }
}
