<?php

if (!function_exists('can')) {
  /**
   * Check if the logged-in user has a specific permission
   * 
   * @param string $permission
   * @return bool
   */
  function can(string $permission): bool
  {
    // Attempt to load PermissionService
    try {
      $permissionService = service('PermissionService');
      if ($permissionService) {
        return $permissionService->can($permission);
      }
    } catch (\Throwable $e) {
      // Service not found or error
    }

    // Fallback: Check session directly
    // Assuming permissions are stored in session ['permissions'] array
    $session = session();
    if (!$session->has('user')) {
      return false;
    }

    $permissions = $session->get('permissions');

    if (!is_array($permissions)) {
      return false;
    }

    // Super Admin wildcard
    if (in_array('*', $permissions)) {
      return true;
    }

    return in_array($permission, $permissions);
  }
}

if (!function_exists('hasRole')) {
  /**
   * Check if the logged-in user has a specific role
   * 
   * @param string $roleName
   * @return bool
   */
  function hasRole(string $roleName): bool
  {
    try {
      $permissionService = service('PermissionService');
      if ($permissionService) {
        return $permissionService->hasRole($roleName);
      }
    } catch (\Throwable $e) {
      // Service not found
    }

    // Fallback: Check session
    $session = session();
    if (!$session->has('user')) {
      return false;
    }

    $roles = $session->get('roles') ?? [];
    return in_array($roleName, $roles);
  }
}
