<?php

namespace App\Controllers\Users;

use App\Controllers\BaseController;
use App\Services\User\RoleService;
use App\Models\RoleModel;
use Exception;

class RoleController extends BaseController
{
    protected RoleService $roleService;
    protected RoleModel $roleModel;

    public function __construct()
    {
        $this->roleService = new RoleService();
        $this->roleModel = new RoleModel();
    }

    /**
     * List all roles.
     */
    public function index(): string
    {
        $data = [
            'roles' => $this->roleModel->findAll(), // Includes system roles filtered by company/system scope in Model
            'title' => 'Roles & Permissions'
        ];

        return view('Roles/index', $data);
    }

    /**
     * Show create role form.
     */
    public function create(): string
    {
        $data = [
            'permissions' => $this->roleService->getAvailablePermissions(),
            'title'       => 'Create Role'
        ];
        return view('Roles/create', $data);
    }

    /**
     * Store new role.
     */
    public function store()
    {
        $rules = [
            'role_name'        => 'required|min_length[3]|max_length[100]',
            'role_description' => 'permit_empty|max_length[255]',
            'permissions'      => 'permit_empty', // Array validation managed manually or via strict rules if needed
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $data = $this->request->getPost();
            
            // Ensure permissions is array
            if (!isset($data['permissions'])) {
                $data['permissions'] = [];
            }

            $this->roleService->createRole($data);

            return redirect()->to('roles')->with('message', 'Role created successfully');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show edit role form.
     */
    public function edit(int $id): string
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Role not found: $id");
        }

        // Check if system role? 
        // We might allow viewing but disable editing specific fields in View, 
        // or Controller blocks update. 
        // Edit view usually allows changing permissions even for system roles? 
        // Prompt says "Check if system role (show as read-only)" and for update "Check if system role (cannot update)".
        // So System roles are effectively READ-ONLY in this UI.

        $data = [
            'role'        => $role,
            'permissions' => $this->roleService->getAvailablePermissions(),
            'rolePermissions' => $this->roleService->getRolePermissions($id),
            'title'       => 'Edit Role'
        ];
        return view('Roles/edit', $data);
    }

    /**
     * Update role.
     */
    public function update(int $id)
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Role not found");
        }

        if ($role['is_system_role']) {
            return redirect()->back()->with('error', 'Cannot update system roles.');
        }

        $rules = [
            'role_name'        => 'required|min_length[3]|max_length[100]',
            'role_description' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $data = $this->request->getPost();
            
            // Handle permissions input
            if (!isset($data['permissions'])) {
                $data['permissions'] = [];
            }

            $this->roleService->updateRole($id, $data);

            return redirect()->to('roles')->with('message', 'Role updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete role.
     */
    public function delete(int $id)
    {
        try {
            $this->roleService->deleteRole($id);
            return redirect()->to('roles')->with('message', 'Role deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show permissions management view (Special permission-only view if needed).
     */
    public function permissions(int $id): string
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
             throw new \CodeIgniter\Exceptions\PageNotFoundException("Role not found");
        }

        $data = [
            'role'            => $role,
            'permissions'     => $this->roleService->getAvailablePermissions(),
            'rolePermissions' => $this->roleService->getRolePermissions($id),
            'title'           => 'Manage Permissions: ' . $role['role_name']
        ];
        return view('Roles/permissions', $data);
    }

    /**
     * Update permissions.
     */
    public function updatePermissions(int $id)
    {
        try {
            $role = $this->roleModel->find($id);
            if ($role['is_system_role']) {
                 throw new Exception("Cannot modify permissions for system roles.");
            }

            $permissions = $this->request->getPost('permissions');
            if (!is_array($permissions)) {
                $permissions = [];
            }

            $this->roleService->updatePermissions($id, $permissions);

            return redirect()->to("roles/permissions/$id")->with('message', 'Permissions updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
