<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
  protected $table = 'audit_logs';
  protected $primaryKey = 'id';
  protected $allowedFields = [
    'company_id',
    'user_id',
    'module',
    'action_type',
    'record_type',
    'record_id',
    'before_data',
    'after_data',
    'ip_address',
    'user_agent',
    'created_at'
  ];
  protected $useTimestamps = false; // created_at is handled by DB default
  protected $returnType = 'array';

  /**
   * Get audit trail for a specific record across all actions
   */
  public function getAuditTrail(string $recordType, int $recordId)
  {
    return $this->select('audit_logs.*, users.username, users.full_name')
      ->join('users', 'users.id = audit_logs.user_id', 'left')
      ->where('record_type', $recordType)
      ->where('record_id', $recordId)
      ->orderBy('audit_logs.created_at', 'DESC')
      ->findAll();
  }

  /**
   * Get recent activity for a specific company
   */
  public function getRecentActivity(int $companyId, int $limit = 50)
  {
    return $this->select('audit_logs.*, users.username, users.full_name')
      ->join('users', 'users.id = audit_logs.user_id', 'left')
      ->where('audit_logs.company_id', $companyId)
      ->orderBy('audit_logs.created_at', 'DESC')
      ->limit($limit)
      ->findAll();
  }

  /**
   * Get activity by a specific user filtered by date range
   */
  public function getUserActivity(int $companyId, int $userId, $fromDate = null, $toDate = null)
  {
    $builder = $this->select('audit_logs.*, users.username, users.full_name')
      ->join('users', 'users.id = audit_logs.user_id', 'left')
      ->where('audit_logs.company_id', $companyId)
      ->where('audit_logs.user_id', $userId);

    if ($fromDate) {
      $builder->where('audit_logs.created_at >=', $fromDate . ' 00:00:00');
    }
    if ($toDate) {
      $builder->where('audit_logs.created_at <=', $toDate . ' 23:59:59');
    }

    return $builder->orderBy('audit_logs.created_at', 'DESC')->findAll();
  }
}
