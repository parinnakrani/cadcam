<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use App\Services\Auth\AuthService;

class AuthFilter implements FilterInterface
{
    /**
     * Check if user is logged in
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = Services::session();

        if (! $session->has('user_id')) {
            // Check for Remember Me Cookie
            $authService = new AuthService();
            if ($authService->attemptAutoLogin()) {
                return; // Session restored, allow access
            }

            // Store intended URL for redirect after login
            $session->set('redirect_url', current_url());

            return redirect()->to(base_url('login'))->with('error', 'Please login to access this page.');
        }

        // Optional: Check if session is valid/active specific logic here
        // CI4 Session handles expiration automatically.
    }

    /**
     * No action needed after request
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
