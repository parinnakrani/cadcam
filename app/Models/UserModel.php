<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UserModel
 *
 * Handles User CRUD and Authentication logic.
 * strictly adheres to .antigravity rules.
 */
class UserModel extends BaseModel
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // We handle it manually as requested, or via 'is_deleted' flag logic
    protected $protectFields    = true;
    protected $allowedFields    = [
        'company_id',
        'username',
        'email',
        'password', // Virtual field for input, unset in callback
        'password_hash', // Mapped from 'password' input
        'full_name',
        'mobile_number', // Schema: mobile_number
        'remember_token',
        'remember_expires_at',
        'profile_photo',
        'adhar_card_number',
        'date_of_joining',
        'employment_status', // Schema enum: Active, Inactive, Suspended
        'failed_login_attempts',
        'last_login_at',
        'last_login_ip',
        'is_deleted'
        // 'role_id' and 'is_system_user' removed (Not in table schema)
        // 'last_failed_login' removed (Not in table schema)
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; // Schema has deleted_at column? Let's check.
    // Schema in Step 514: `is_deleted` BOOLEAN. No `deleted_at` column in `users` table output lines 81-105.
    // So distinct Soft Delete implementation using `is_deleted` flag.

    // Validation
    protected $validationRules = [
        'username'      => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
        'email'         => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password'      => 'required|min_length[8]', // Virtual field for validation
        'full_name'     => 'required|min_length[3]',
        'mobile_number' => 'required|regex_match[/^[0-9]{10}$/]',
        'company_id'    => 'required|integer',
        'id'            => 'permit_empty|is_natural_no_zero'
    ];
    
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hash password before saving
     */
    protected function hashPassword(array $data): array
    {
        if (! isset($data['data']['password'])) {
            return $data;
        }

        $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        unset($data['data']['password']);

        return $data;
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?array
    {
        $this->applyCompanyFilter();
        return $this->where($this->table . '.username', $username)
                    ->where($this->table . '.is_deleted', 0)
                    ->first();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        $this->applyCompanyFilter();
        return $this->where($this->table . '.email', $email)
                    ->where($this->table . '.is_deleted', 0)
                    ->first();
    }

    /**
     * Increment failed login attempts
     */
    public function incrementFailedAttempts(int $userId): bool
    {
        // Note: last_failed_login column does not exist in schema, skipping timestamp update
        return $this->builder()
                    ->where('id', $userId)
                    ->increment('failed_login_attempts');
    }

    /**
     * Reset failed login attempts
     */
    public function resetFailedAttempts(int $userId): bool
    {
        return $this->update($userId, [
            'failed_login_attempts' => 0
            // 'last_failed_login' => null // Column not in schema
        ]);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->update($userId, [
            'last_login_at' => date('Y-m-d H:i:s')
        ]);
    }


}
