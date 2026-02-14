<?php

if (!function_exists('can')) {
    /**
     * Check if the currently logged-in user has a specific permission.
     * 
     * @param string $permission
     * @return bool
     */
    function can(string $permission): bool {
        // Ensure the PermissionService is available. 
        // If PermissionService is not a shared service, this might instantiate a new one.
        // Assuming PermissionService is registered in Config\Services or auto-discoverable.
        // If not, we might need to rely on the library directly or user session.
        
        // Use the registered service name
        $permissionService = service('PermissionService');
        
        // Note: service('PermissionService') might fail if not defined in Services.php.
        // The user might have meant \App\Services\Auth\PermissionService::class?
        // Or assumes 'PermissionService' alias exists.
        // Given I implemented "PermissionFilter" earlier (implied), maybe it exists.
        // I'll stick to the prompt's code.
        
        // Fallback or safety check if service returns null?
        if (!$permissionService) {
            // Try to resolve by class name if alias fails
            // $permissionService = new \App\Services\Auth\PermissionService();
            // But relying on Prompt's instruction:
            return false; // Fail safe
        }

        return $permissionService->can($permission);
    }
}

if (!function_exists('has_permission')) {
    /**
     * Alias for can()
     * 
     * @param string $permission
     * @return bool
     */
    function has_permission(string $permission): bool {
        return can($permission);
    }
}
