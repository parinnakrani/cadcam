<?php

namespace App\Models;

use CodeIgniter\Model;

class StateModel extends Model
{
    protected $table            = 'states';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Assuming states are hard-delete or no delete? Prompt says 'is_active'.
    protected $protectFields    = true;
    protected $allowedFields    = [
        'state_name', 'state_code', 'country_name', 'is_active'
    ];

    // Dates - usually metadata tables don't need timestamps, but migration didn't add them.
    // Migration Step 2226: 'id', 'state_name', 'state_code', 'country_name', 'is_active'. NO created_at/updated_at.
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'state_name' => 'required|min_length[2]|max_length[100]',
        'state_code' => 'permit_empty|max_length[10]', // GST Code
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
