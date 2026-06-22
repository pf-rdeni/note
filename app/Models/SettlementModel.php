<?php

namespace App\Models;

use CodeIgniter\Model;

class SettlementModel extends Model
{
    protected $table            = 'settlements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'period_id', 
        'trip_id', 
        'from_user_id', 
        'to_user_id', 
        'amount', 
        'status', 
        'paid_at', 
        'proof_image', 
        'note'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'trip_id'      => 'required|numeric',
        'from_user_id' => 'required|numeric',
        'to_user_id'   => 'required|numeric',
        'amount'       => 'required|numeric|greater_than[0]',
        'status'       => 'required|in_list[pending,paid]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
