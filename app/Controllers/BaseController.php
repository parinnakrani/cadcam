<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Services\Auth\PermissionService;

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

  // Multi-tenant support
  protected ?int $companyId = null;
  protected ?int $userId = null;
  protected ?array $userData = null;

  // RBAC support
  protected ?PermissionService $permissions = null;
  protected array $viewData = [];

  /**
   * @return void
   */
  public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
  {
    // Do Not Edit This Line
    parent::initController($request, $response, $logger);

    // Boot permissions if user is logged in
    $session = \Config\Services::session();
    if ($session->has('user_id')) {
      $this->userId    = (int) $session->get('user_id');
      $this->companyId = (int) $session->get('company_id');

      // Boot PermissionService
      $this->permissions = \Config\Services::PermissionService();
      $this->permissions->boot($this->userId);

      // Auto-inject menu data for all views
      $this->viewData = [
        'permissions'       => $this->permissions,
        'invoiceMenuItems'  => $this->permissions->getInvoiceMenuItems(),
        'challanMenuItems'  => $this->permissions->getChallanMenuItems(),
        'masterMenuItems'   => $this->permissions->getMasterMenuItems(),
        'customerMenuItems' => $this->permissions->getCustomerMenuItems(),
        'reportMenuItems'   => $this->permissions->getReportMenuItems(),
        'ledgerMenuItems'   => $this->permissions->getLedgerMenuItems(),
      ];
    }
  }

  // ─────────────────────────────────────────────────────────
  // PERMISSION HELPERS
  // ─────────────────────────────────────────────────────────

  /**
   * Check if user has specific permission.
   *
   * @param string $permission e.g. "invoices.account.print"
   * @return bool
   */
  protected function hasPermission(string $permission): bool
  {
    return can($permission);
  }

  /**
   * Gate — abort if user cannot perform the action.
   * Shortcut for abort_if_cannot().
   *
   * @param string $permission
   * @param string $redirectTo
   * @return void
   */
  protected function gate(string $permission, string $redirectTo = 'dashboard'): void
  {
    abort_if_cannot($permission, $redirectTo);
  }

  /**
   * Resolve invoice_type DB value to sub_module key.
   * e.g. "Accounts Invoice" → "account"
   */
  protected function resolveInvoiceSub(string $invoiceType): string
  {
    return $this->permissions
      ? $this->permissions->resolveInvoiceSubModule($invoiceType)
      : 'all';
  }

  /**
   * Resolve challan_type DB value to sub_module key.
   * e.g. "Rhodium" → "rhodium"
   */
  protected function resolveChallanSub(string $challanType): string
  {
    return $this->permissions
      ? $this->permissions->resolveChallanSubModule($challanType)
      : 'all';
  }

  // ─────────────────────────────────────────────────────────
  // VIEW RENDERING
  // ─────────────────────────────────────────────────────────

  /**
   * Render a view with auto-injected permission/menu data.
   * Merges $viewData (sidebar menus, permissions) with controller-specific data.
   *
   * @param string $view  View path, e.g. "invoices/index"
   * @param array  $data  Controller-specific data
   * @return string
   */
  protected function render(string $view, array $data = []): string
  {
    return view($view, array_merge($this->viewData, $data));
  }

  // ─────────────────────────────────────────────────────────
  // JSON RESPONSE HELPERS
  // ─────────────────────────────────────────────────────────

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
