<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class PermissionFilter implements FilterInterface
{
  /**
   * Check for required permission before route execution.
   *
   * Usage in routes:
   *   'filter' => 'permission:invoices.all.list'
   *   'filter' => 'permission:invoices.account.list'
   *
   * @param RequestInterface $request
   * @param array|null       $arguments Permission strings to check
   * @return mixed
   */
  public function before(RequestInterface $request, $arguments = null)
  {
    $session = Services::session();

    // Check if user is authenticated
    if (!$session->has('user_id')) {
      if ($request instanceof \CodeIgniter\HTTP\IncomingRequest && $request->isAJAX()) {
        return Services::response()
          ->setStatusCode(401)
          ->setJSON([
            'success' => false,
            'status'  => 401,
            'message' => 'Authentication required.',
          ]);
      }

      return redirect()->to(base_url('login'));
    }

    // Boot the PermissionService
    $permissionService = service('PermissionService');
    $permissionService->boot((int) $session->get('user_id'));

    // If no permission arguments specified, just auth check (pass through)
    if (empty($arguments)) {
      return;
    }

    // Check each required permission
    foreach ($arguments as $permission) {
      if (!$permissionService->can($permission)) {
        // Log unauthorized access attempt
        try {
          $auditService = new \App\Services\Audit\AuditService();
          $auditService->log('access_denied', "Unauthorized access attempt by User ID: " . $session->get('user_id') . " for permission: {$permission} at " . current_url());
        } catch (\Throwable $e) {
          // Audit logging should not break the request
        }

        // AJAX → JSON 403
        if ($request instanceof \CodeIgniter\HTTP\IncomingRequest && $request->isAJAX()) {
          return Services::response()
            ->setStatusCode(403)
            ->setJSON([
              'success' => false,
              'status'  => 403,
              'message' => 'You do not have permission to access this resource.',
            ]);
        }

        // Normal → redirect to dashboard with flash error
        $session->setFlashdata('error', 'You do not have permission to access this resource.');
        return redirect()->to(base_url('dashboard'));
      }
    }
  }

  /**
   * After filter — do nothing.
   */
  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
  {
    // No post-processing needed
  }
}
