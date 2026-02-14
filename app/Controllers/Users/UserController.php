<?php

namespace App\Controllers\Users;

use App\Controllers\BaseController;
use App\Services\User\UserService;
use App\Services\User\RoleService;
use App\Models\UserModel;
use Exception;

class UserController extends BaseController
{
    protected UserService $userService;
    protected RoleService $roleService;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->roleService = new RoleService();
        $this->userModel = new UserModel();
    }

    /**
     * List all users.
     * Data is loaded via AJAX for DataTables.
     */
    public function index(): string
    {
        return view('Users/index', ['title' => 'Users List']);
    }

    /**
     * Show create user form.
     */
    public function create(): string
    {
        $data = [
            'roles' => $this->roleService->getAllRoles(), // Get available roles via Service
            'title' => 'Create User'
        ];
        return view('Users/create', $data);
    }

    /**
     * Store new user.
     */
    public function store()
    {
        $rules = [
            'full_name'     => 'required|min_length[3]',
            'username'      => 'required|min_length[3]|is_unique[users.username]',
            'email'         => 'required|valid_email|is_unique[users.email]',
            'mobile_number' => 'required|regex_match[/^[0-9]{10}$/]',
            'password'      => 'required|min_length[8]',
            'role_ids'      => 'permit_empty', // Array handling might need custom rule or just check in controller
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $data = $this->request->getPost();
            
            // Handle role input if array or single
            if (!isset($data['role_ids'])) { 
                 $data['role_ids'] = []; 
            }

            $this->userService->createUser($data);

            return redirect()->to('users')->with('message', 'User created successfully');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show edit user form.
     */
    public function edit(int $id): string
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("User not found: $id");
        }

        $userRoles = $this->userService->getUserRoles($id);
        $roleIds = array_column($userRoles, 'id');

        $data = [
            'user'      => $user,
            'roles'     => $this->roleService->getAllRoles(), // Get all roles via Service
            'userRoles' => $roleIds,
            'title'     => 'Edit User'
        ];
        return view('Users/edit', $data);
    }

    /**
     * Update user.
     */
    public function update(int $id)
    {
        // Validation rules (exclude unique check for current user)
        $rules = [
            'full_name'     => 'required|min_length[3]',
            'email'         => "required|valid_email|is_unique[users.email,id,$id]",
            'mobile_number' => 'required|regex_match[/^[0-9]{10}$/]',
            // Password optional on update
            'password'      => 'permit_empty|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $data = $this->request->getPost();
            
            // Handle empty password (remove from data to avoid unhashing or empty hash)
            if (empty($data['password'])) {
                unset($data['password']);
            }
            
            $this->userService->updateUser($id, $data);

            return redirect()->to('users')->with('message', 'User updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete user.
     */
    public function delete(int $id)
    {
        try {
            $this->userService->deleteUser($id);
            return redirect()->to('users')->with('message', 'User deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show change password form.
     */
    public function changePassword(int $id): string
    {
         $data = [
            'userId' => $id,
            'title'  => 'Change Password'
        ];
        return view('Users/change_password', $data);
    }

    /**
     * Update password.
     */
    public function updatePassword(int $id)
    {
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $current = $this->request->getPost('current_password');
            $new     = $this->request->getPost('new_password');

            $this->userService->changePassword($id, $current, $new);

            return redirect()->to('users')->with('message', 'Password changed successfully');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
