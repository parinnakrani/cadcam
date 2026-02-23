<?php

if (!function_exists('can')) {
  /**
   * Check if the logged-in user has a specific permission.
   * Uses the centralized PermissionService with wildcard cascade.
   *
   * @param string $permission e.g. "invoices.account.print"
   * @return bool
   */
  function can(string $permission): bool
  {
    try {
      $permissionService = service('PermissionService');
      if ($permissionService) {
        return $permissionService->can($permission);
      }
    } catch (\Throwable $e) {
      // Service not available
    }

    return false;
  }
}

if (!function_exists('cannot')) {
  /**
   * Opposite of can() — returns true if user does NOT have the permission.
   *
   * @param string $permission
   * @return bool
   */
  function cannot(string $permission): bool
  {
    return !can($permission);
  }
}

if (!function_exists('can_any')) {
  /**
   * Check if the user has ANY permission starting with the given prefix.
   * Useful for sidebar/menu visibility.
   *
   * @param string $prefix e.g. "invoices", "invoices.account", "masters"
   * @return bool
   */
  function can_any(string $prefix): bool
  {
    try {
      $permissionService = service('PermissionService');
      if ($permissionService) {
        return $permissionService->canAny($prefix);
      }
    } catch (\Throwable $e) {
      // Service not available
    }

    return false;
  }
}

if (!function_exists('abort_if_cannot')) {
  /**
   * Check permission and abort if not authorized.
   * For AJAX: returns JSON 403 response.
   * For normal requests: sets flash error and redirects.
   *
   * @param string $permission
   * @param string $redirectTo Path to redirect to (default: 'dashboard')
   * @return void
   */
  function abort_if_cannot(string $permission, string $redirectTo = 'dashboard'): void
  {
    if (can($permission)) {
      return; // Permission granted, do nothing
    }

    $request = \Config\Services::request();

    // AJAX request → JSON 403
    if ($request->isAJAX()) {
      $response = \Config\Services::response();
      $response->setStatusCode(403)
        ->setJSON([
          'success' => false,
          'status'  => 403,
          'message' => 'You do not have permission to perform this action.',
        ])
        ->send();
      exit;
    }

    // Normal request → redirect with flash error
    session()->setFlashdata('error', 'You do not have permission to access this resource.');
    $response = \Config\Services::response();
    $response->redirect(base_url($redirectTo));
    $response->send();
    exit;
  }
}

if (!function_exists('hasRole')) {
  /**
   * Check if the logged-in user has a specific role.
   *
   * @param string $roleName
   * @return bool
   */
  function hasRole(string $roleName): bool
  {
    $session = session();
    if (!$session->has('user_id')) {
      return false;
    }

    $roles = $session->get('roles') ?? [];
    return in_array($roleName, $roles);
  }
}
