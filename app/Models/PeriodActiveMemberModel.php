<?php

namespace App\Models;

use CodeIgniter\Model;

class PeriodActiveMemberModel extends Model
{
    protected $table            = 'period_active_members';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['period_id', 'user_id', 'created_at'];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'period_id' => 'required|numeric',
        'user_id'   => 'required|numeric',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
