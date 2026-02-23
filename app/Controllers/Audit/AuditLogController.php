<?php

namespace App\Controllers\Audit;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\UserModel;

class AuditLogController extends BaseController
{
  protected $auditModel;
  protected $userModel;

  public function __construct()
  {
    $this->auditModel = new AuditLogModel();
    $this->userModel = new UserModel();
  }

  public function index()
  {
    $this->gate('audit.logs.all.list');

    $session = session();
    $companyId = $session->get('company_id');

    $data = [
      'logs' => $this->auditModel->getRecentActivity($companyId, 100),
      'title' => 'System Audit Logs'
    ];

    if ($this->permissions) {
      $data['action_flags'] = $this->permissions->getActionFlags('audit', 'logs.all');
    }

    return $this->render('audit/index', $data);
  }

  public function recordAuditTrail($recordType, $recordId)
  {
    $this->gate('audit.logs.all.view');
    $data = [
      'logs' => $this->auditModel->getAuditTrail($recordType, $recordId),
      'recordType' => $recordType,
      'recordId' => $recordId,
      'title' => "Audit Trail: $recordType #$recordId"
    ];

    if ($this->permissions) {
      $data['action_flags'] = $this->permissions->getActionFlags('audit', 'logs.all');
    }

    return $this->render('audit/index', $data); // Reuse index view or create specific one. Reusing index with filtered data is easiest.
  }

  public function userActivity($userId)
  {
    $this->gate('audit.logs.all.view');

    $session = session();
    $companyId = $session->get('company_id');

    $user = $this->userModel->find($userId);

    $data = [
      'logs' => $this->auditModel->getUserActivity($companyId, $userId),
      'title' => 'Activity Log: ' . ($user['full_name'] ?? 'Unknown User')
    ];

    if ($this->permissions) {
      $data['action_flags'] = $this->permissions->getActionFlags('audit', 'logs.all');
    }

    return $this->render('audit/index', $data);
  }
}
