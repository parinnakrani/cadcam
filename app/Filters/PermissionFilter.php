<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use App\Services\Auth\PermissionService;
use App\Services\Audit\AuditService;

class PermissionFilter implements FilterInterface
{
    /**
     * Check for required permission
     *
     * @param RequestInterface $request
     * @param array|null       $arguments List of required permissions (usually single)
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            return; // No permission required
        }

        $permission = array_shift($arguments);
        $permissionService = new PermissionService();
        
        // Check if user has permission
        if (! $permissionService->can($permission)) {
            
            // Log Unauthorized Access
            $auditService = new AuditService();
            $user = Services::session()->get('user_id');
            
            $auditService->log('access_denied', "Unauthorized access attempt by User ID: {$user}", [
                'user_id' => $user,
                'required_permission' => $permission,
                'url' => current_url()
            ]);

            // Return 403 Forbidden
            $response = Services::response();
            
            if ($request instanceof \CodeIgniter\HTTP\IncomingRequest && $request->isAJAX()) {
                return $response->setJSON([
                    'status' => 403,
                    'error'  => 'Access Denied',
                    'message' => 'You do not have permission to access this resource.'
                ])->setStatusCode(403);
            }

            // For standard requests, show 403 view or generic message
            // Ideally load a view, but for now simple output
            return $response->setStatusCode(403)->setBody('<h1>403 Forbidden</h1><p>You do not have permission to access this resource.</p>');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
