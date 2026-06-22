<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['trip_id', 'period_id', 'date', 'description', 'amount', 'paid_by', 'type', 'receipt_image', 'created_by'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'trip_id'     => 'required|numeric',
        'period_id'   => 'permit_empty|numeric',
        'date'        => 'required|valid_date[Y-m-d]',
        'description' => 'required|min_length[3]|max_length[255]',
        'amount'      => 'required|numeric|greater_than[0]',
        'paid_by'     => 'required|numeric',
        'type'        => 'required|in_list[shared,individual]',
        'created_by'  => 'required|numeric'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
