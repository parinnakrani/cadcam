<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Services\Auth\AuthService;

class LogoutController extends BaseController
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Process Logout
     * POST /logout
     */
    public function logout()
    {
        // AuthService handles session destruction and audit logging
        $this->authService->logout();

        return redirect()->to(base_url('login'))->with('message', 'You have been logged out successfully.');
    }
}
