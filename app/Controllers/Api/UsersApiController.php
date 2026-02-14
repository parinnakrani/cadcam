<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\User\UserService;
use App\Services\User\RoleService;

/**
 * UsersApiController
 * 
 * Handles AJAX requests for Users DataTable.
 */
class UsersApiController extends BaseController
{
    protected UserService $userService;
    protected RoleService $roleService;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->roleService = new RoleService();
    }

    /**
     * DataTables server-side processing endpoint.
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function list()
    {
        try {
            $request = $this->request;

            // DataTables parameters
            $draw   = (int) $request->getGet('draw');
            $start  = (int) $request->getGet('start');
            $length = (int) $request->getGet('length');
            $search = $request->getGet('search')['value'] ?? '';
            $order  = $request->getGet('order') ?? [];
            
            // Custom filters
            $roleFilter   = $request->getGet('role') ?? '';
            $statusFilter = $request->getGet('status') ?? '';

            // Column mapping for ordering (matches table: User, Role, Status, Mobile, Actions)
            $columns = ['full_name', 'role_names', 'employment_status', 'mobile_number'];
            $orderColumn = 'full_name'; // default
            if (isset($order[0]['column']) && isset($columns[$order[0]['column']])) {
                $orderColumn = $columns[$order[0]['column']];
            }
            $orderDir = isset($order[0]['dir']) && $order[0]['dir'] === 'asc' ? 'ASC' : 'DESC';

            // Get data
            $result = $this->userService->getDataTableUsers([
                'search' => $search,
                'role'   => $roleFilter,
                'status' => $statusFilter,
                'start'  => $start,
                'length' => $length,
                'orderColumn' => $orderColumn,
                'orderDir'    => $orderDir
            ]);

            // Format response for DataTables
            $data = [];
            foreach ($result['data'] as $user) {
                $data[] = [
                    'id'          => $user['id'],
                    'full_name'   => $user['full_name'] ?? '',
                    'email'       => $user['email'] ?? '',
                    'role'        => $user['role_names'] ?? 'N/A',
                    'status'      => $user['employment_status'] ?? '',
                    'mobile'      => $user['mobile_number'] ?? '',
                    'edit_url'    => base_url('users/' . $user['id'] . '/edit'),
                    'password_url'=> base_url('users/' . $user['id'] . '/password'),
                    'delete_url'  => base_url('users/' . $user['id'] . '/delete'),
                ];
            }

            return $this->response->setJSON([
                'draw'            => $draw,
                'recordsTotal'    => $result['totalRecords'],
                'recordsFiltered' => $result['filteredRecords'],
                'data'            => $data
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Users API error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw'            => (int) ($this->request->getGet('draw') ?? 0),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'An error occurred while fetching data.'
            ]);
        }
    }

    /**
     * Get roles for filter dropdown.
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getRoles()
    {
        $roles = $this->roleService->getAllRoles();
        return $this->response->setJSON($roles);
    }

    /**
     * Get user statistics.
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getStats()
    {
        $stats = $this->userService->getUserStats();
        return $this->response->setJSON($stats);
    }
}
