<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Services\Auth\AuthService;
use App\Exceptions\AuthenticationException;
use App\Exceptions\AccountLockedException;

class LoginController extends BaseController
{
    protected $authService;

    public function __construct()
    {
        // Instantiate AuthService (Dependencies injected inside AuthService constructor if needed)
        // Since we didn't register it in Services, manual instantiation is fine for now
        $this->authService = new AuthService();
    }

    /**
     * Show Login Form
     * GET /login
     */
    public function showLoginForm()
    {
        helper('form');

        // If already logged in, redirect to dashboard
        if ($this->authService->isLoggedIn()) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('Auth/login', [
            'page_title' => 'Login'
        ]);
    }

    /**
     * Process Login
     * POST /login
     */
    public function authenticate()
    {
        // 1. Validation
        $rules = [
            'username' => 'required|min_length[3]',
            'password' => 'required|min_length[8]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 2. Attempt Login
        try {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            $remember = (bool) $this->request->getPost('remember');

            $user = $this->authService->login($username, $password, $remember);

            if ($user) {
                // Success
                return redirect()->to(base_url('dashboard'))->with('message', 'Welcome back!');
            }

        } catch (AccountLockedException $e) {
            // Account Locked
            return redirect()->back()->withInput()->with('error', $e->getMessage());

        } catch (AuthenticationException $e) {
            // Invalid Credentials
            return redirect()->back()->withInput()->with('error', $e->getMessage());
            
        } catch (\Exception $e) {
            // General Error
            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred. Please try again.');
        }

        // Fallback (Should be caught by exceptions, but just in case)
        return redirect()->back()->withInput()->with('error', 'Login failed.');
    }

    /**
     * Logout
     * GET /logout
     */
    public function logout()
    {
        $this->authService->logout();
        return redirect()->to(base_url('login'))->with('message', 'You have been logged out.');
    }
}
