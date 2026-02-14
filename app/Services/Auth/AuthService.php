<?php

namespace App\Services\Auth;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Services\Audit\AuditService;
use App\Exceptions\AuthenticationException;
use App\Exceptions\AccountLockedException;
use Config\Services;

class AuthService
{
    protected $userModel;
    protected $roleModel;
    protected $auditService;
    protected $session;
    protected $db;

    public function __construct(
        ?UserModel $userModel = null,
        ?RoleModel $roleModel = null,
        ?AuditService $auditService = null
    ) {
        $this->userModel    = $userModel ?? new UserModel();
        $this->roleModel    = $roleModel ?? new RoleModel();
        $this->auditService = $auditService ?? new AuditService(); // Using created AuditService
        $this->session      = Services::session();
        $this->db           = \Config\Database::connect();
        helper('cookie');
    }

    /**
     * Authenticate user and set session
     *
     * @param string $username
     * @param string $password
     * @param bool $remember
     * @return array|false Returns user array on success, false on failure (or throws exception)
     * @throws AuthenticationException|AccountLockedException
     */
    public function login(string $username, string $password, bool $remember = false): ?array
    {
        // 1. Find user by username
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            $this->auditService->log('login_failed', "User not found: {$username}", ['username' => $username]);
            throw new AuthenticationException("Invalid credentials.");
        }

        // 2. Check failed attempts / Account Lock
        if ($user['failed_login_attempts'] >= 5) {
            $this->auditService->log('login_locked', "Account locked for user: {$username} (ID: {$user['id']})", 
                ['user_id' => $user['id'], 'attempts' => $user['failed_login_attempts']]
            );
            throw new AccountLockedException("Account is locked due to too many failed login attempts.");
        }

        // 3. Check Active Status
        // Schema uses 'employment_status' enum ('Active', 'Inactive', 'Suspended')
        if ($user['employment_status'] !== 'Active') {
            $this->auditService->log('login_inactive', "Inactive user login attempt: {$username} (ID: {$user['id']})",
                ['user_id' => $user['id'], 'status' => $user['employment_status']]
            );
            throw new AuthenticationException("Account is inactive.");
        }

        // 4. Verify password
        if (!$this->validatePassword($password, $user['password_hash'])) {
            $this->handleFailedLogin((int) $user['id']);
            throw new AuthenticationException("Invalid credentials.");
        }

        // 5. Login Success: Reset failures & Update login stats
        $this->userModel->resetFailedAttempts((int) $user['id']);
        $this->userModel->updateLastLogin((int) $user['id']);

        // 6. Set Session
        $this->setSession($user);

        // 7. Handle Remember Me
        if ($remember) {
            $this->rememberUser($user['id']);
        }

        // 8. Audit Log
        $this->auditService->log('login_success', "User logged in: {$username} (ID: {$user['id']})",
            ['user_id' => $user['id'], 'ip' => service('request')->getIPAddress()]
        );

        return $user;
    }

    /**
     * Logout current user
     */
    public function logout(): bool
    {
        $currentUser = $this->getCurrentUser();
        
        if ($currentUser) {
            $this->auditService->log('logout', "User logged out: {$currentUser['username']} (ID: {$currentUser['id']})",
                ['user_id' => $currentUser['id']]
            );
        }

        // Clear Remember Me
        if ($userId = session()->get('user_id')) {
            $this->forgetUser($userId);
        }

        $this->session->destroy();
        return true;
    }

    /**
     * Get current user details from session
     */
    public function getCurrentUser(): ?array
    {
        if (!$this->session->has('user_id')) {
            return null;
        }

        return [
            'id'             => $this->session->get('user_id'),
            'username'       => $this->session->get('username'),
            'company_id'     => $this->session->get('company_id'),
            'full_name'      => $this->session->get('full_name'),
            'is_super_admin' => $this->session->get('is_super_admin'),
            'permissions'    => $this->session->get('permissions') ?? []
        ];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool
    {
        return $this->session->has('user_id');
    }

    /**
     * Check if current user is Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->session->get('is_super_admin') === true;
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Set session data for logged in user
     */
    private function setSession(array $user): void
    {
        // Fetch Role & Permissions
        $roleId = $this->getUserRoleId((int) $user['id']);
        $permissions = [];
        $isSuperAdmin = false;

        if ($roleId) {
            $permissions = $this->roleModel->getPermissions($roleId);
            
            // Check for Super Admin (Wildcard Permission)
            if (in_array('*', $permissions)) {
                $isSuperAdmin = true;
            }
        }

        $sessionData = [
            'user_id'        => $user['id'],
            'username'       => $user['username'],
            'company_id'     => $user['company_id'], // System User has company_id=1
            'full_name'      => $user['full_name'],
            'is_super_admin' => $isSuperAdmin,
            'permissions'    => $permissions,
            'logged_in'      => true
        ];

        $this->session->set($sessionData);
    }

    /**
     * Helper to get Role ID for a user from join table
     */
    private function getUserRoleId(int $userId): ?int
    {
        $row = $this->db->table('user_roles')
                        ->select('role_id')
                        ->where('user_id', $userId)
                        ->get() // Use get() directly on builder
                        ->getRow();

        return $row ? (int) $row->role_id : null;
    }

    /**
     * Handle failed login logic
     */
    private function handleFailedLogin(int $userId): void
    {
        $this->userModel->incrementFailedAttempts($userId);
        
        $this->auditService->log('login_failed', "Failed password attempt for user ID: {$userId}",
            ['user_id' => $userId]
        );
    }

    /**
     * Verify password hash
     */
    private function validatePassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    /**
     * Set Remember Me Token
     */
    protected function rememberUser(int $userId)
    {
        // 30 Days Expiry
        $expiresAt = time() + (86400 * 30);
        $token = bin2hex(random_bytes(16));
        $expiresDate = date('Y-m-d H:i:s', $expiresAt);

        // Save to DB
        $this->userModel->update($userId, [
            'remember_token'      => $token,
            'remember_expires_at' => $expiresDate
        ]);

        // Set Cookie (Name, Value, Expiry)
        set_cookie('remember_token', $token, 86400 * 30);
        set_cookie('user_id', (string)$userId, 86400 * 30);
    }

    /**
     * Clear Remember Me Token
     */
    protected function forgetUser(int $userId)
    {
        $this->userModel->update($userId, [
            'remember_token'      => null,
            'remember_expires_at' => null
        ]);
        delete_cookie('remember_token');
        delete_cookie('user_id');
    }

    /**
     * Attempt Auto Login from Cookie
     */
    public function attemptAutoLogin(): bool
    {
        helper('cookie');
        $cookieToken = get_cookie('remember_token');
        $cookieUserId = get_cookie('user_id');

        if (! $cookieToken || ! $cookieUserId) {
            return false;
        }

        $user = $this->userModel->find($cookieUserId);

        if (! $user) {
            return false;
        }

        // Validate Token and Expiry
        if ($user['remember_token'] === $cookieToken &&
            strtotime($user['remember_expires_at']) > time()) {
            
            // Login User
            $this->setSession($user);
            
            // Refresh Token (Optional logic, skipping for now to keep simple)
            
            return true;
        }

        return false;
    }
}
