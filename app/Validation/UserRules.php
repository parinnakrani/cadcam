<?php

namespace App\Validation;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class UserRules
{
    /**
     * Check if username is unique, optionally ignoring a specific ID.
     * Usage: unique_username[users.username,id,{id}] or just unique_username[users.username]
     * Actually, standard syntax: unique_username[table.field.ignore_field] 
     * where ignore_field will be matched against data[ignore_field]
     */
    public function unique_username(string $str, string $fields, array $data): bool
    {
        // Parse params: table.field.id_field
        // Example: users.username.id
        $parts = explode('.', $fields);
        
        $table = $parts[0] ?? 'users';
        $field = $parts[1] ?? 'username';
        $idField = $parts[2] ?? 'id';

        $db = Database::connect();
        $builder = $db->table($table);
        $builder->where($field, $str);
        
        // If ignore ID is present in data
        if (isset($data[$idField])) {
            $builder->where($idField . ' !=', $data[$idField]);
        }
        
        // Also ensure not deleted? Usually yes for uniqueness in active records.
        // Assuming soft delete column 'is_deleted' exists.
        if ($db->fieldExists('is_deleted', $table)) {
            $builder->where('is_deleted', 0);
        }

        return $builder->countAllResults() === 0;
    }

    /**
     * Check if email is unique, optionally ignoring a specific ID.
     */
    public function unique_email(string $str, string $fields, array $data): bool
    {
        // Re-use logic or duplicate for clarity.
        // Logic is identical to unique_username, just for email field.
        // We can just call unique_username logic if params are passed correct.
        return $this->unique_username($str, $fields, $data);
    }

    /**
     * Validate strong password.
     * Min 8 chars, 1 upper, 1 lower, 1 number, 1 special.
     */
    public function strong_password(string $str): bool
    {
        // Min 8
        if (strlen($str) < 8) {
            return false;
        }

        // 1 Uppercase
        if (!preg_match('/[A-Z]/', $str)) {
            return false;
        }

        // 1 Lowercase
        if (!preg_match('/[a-z]/', $str)) {
            return false;
        }

        // 1 Number
        if (!preg_match('/[0-9]/', $str)) {
            return false;
        }

        // 1 Special character
        if (!preg_match('/[^a-zA-Z0-9]/', $str)) {
            return false;
        }

        return true;
    }

    /**
     * Validate mobile number (Indian format).
     * 10 digits.
     */
    public function valid_mobile(string $str): bool
    {
        return (bool) preg_match('/^[0-9]{10}$/', $str);
    }

    /**
     * Validate username format.
     * Alphanumeric and underscore only. Min 3, Max 50.
     */
    public function valid_username(string $str): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9_]{3,50}$/', $str);
    }
}
