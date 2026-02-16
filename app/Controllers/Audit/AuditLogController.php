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
    // Permission check can be added here or via filters

    $session = session();
    if (!$session->has('user')) {
      return redirect()->to('/login');
    }
    $companyId = $session->get('user')['company_id'];

    $data = [
      'logs' => $this->auditModel->getRecentActivity($companyId, 100),
      'title' => 'System Audit Logs'
    ];

    return view('audit/index', $data);
  }

  public function recordAuditTrail($recordType, $recordId)
  {
    $data = [
      'logs' => $this->auditModel->getAuditTrail($recordType, $recordId),
      'recordType' => $recordType,
      'recordId' => $recordId,
      'title' => "Audit Trail: $recordType #$recordId"
    ];

    return view('audit/index', $data); // Reuse index view or create specific one. Reusing index with filtered data is easiest.
  }

  public function userActivity($userId)
  {
    $session = session();
    $companyId = $session->get('user')['company_id'];

    $user = $this->userModel->find($userId);

    $data = [
      'logs' => $this->auditModel->getUserActivity($companyId, $userId),
      'title' => 'Activity Log: ' . ($user['full_name'] ?? 'Unknown User')
    ];

    return view('audit/index', $data);
  }
}
