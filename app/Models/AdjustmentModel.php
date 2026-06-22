<?php

namespace App\Models;

use CodeIgniter\Model;

class AdjustmentModel extends Model
{
    protected $table            = 'transaction_adjustments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['transaction_id', 'target_user_id', 'amount', 'note'];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'transaction_id' => 'required|numeric',
        'target_user_id' => 'required|numeric',
        'amount'         => 'required|numeric|greater_than[0]',
        'note'           => 'permit_empty|max_length[255]'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
