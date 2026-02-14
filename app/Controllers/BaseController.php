<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['permission'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;
    
    // Multi-tenant support
    protected ?int $companyId = null;
    protected ?int $userId = null;
    protected ?array $userData = null;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
        
        // Initialize multi-tenant context
        // TODO: Implement actual authentication - this is a placeholder
        // $this->companyId = session()->get('company_id');
        // $this->userId = session()->get('user_id');
        // $this->userData = session()->get('user_data');
    }
    
    /**
     * Check if user has specific permission
     * 
     * @param string $permission Permission identifier (e.g., 'invoice.create', 'product.delete')
     * @return bool
     */
    protected function hasPermission(string $permission): bool
    {
        // TODO: Implement actual permission checking logic
        // This should check against user roles and permissions from database
        return true; // Placeholder - always returns true for now
    }
    
    /**
     * Return JSON success response
     * 
     * @param string $message Success message
     * @param mixed $data Optional data payload
     * @param int $status HTTP status code (default: 200)
     * @return ResponseInterface
     */
    protected function success(string $message, $data = null, int $status = 200): ResponseInterface
    {
        return $this->jsonResponse($status, $message, $data);
    }
    
    /**
     * Return JSON error response
     * 
     * @param string $message Error message
     * @param int $status HTTP status code (default: 400)
     * @param mixed $errors Optional error details
     * @return ResponseInterface
     */
    protected function error(string $message, int $status = 400, $errors = null): ResponseInterface
    {
        $payload = [
            'status' => $status,
            'message' => $message,
        ];
        
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }
        
        return $this->response
            ->setStatusCode($status)
            ->setJSON($payload);
    }
    
    /**
     * Generic JSON response helper
     * 
     * @param int $status HTTP status code
     * @param string $message Response message
     * @param mixed $data Optional data payload
     * @return ResponseInterface
     */
    protected function jsonResponse(int $status, string $message, $data = null): ResponseInterface
    {
        $payload = [
            'status' => $status,
            'message' => $message,
        ];
        
        if ($data !== null) {
            $payload['data'] = $data;
        }
        
        return $this->response
            ->setStatusCode($status)
            ->setJSON($payload);
    }
}
