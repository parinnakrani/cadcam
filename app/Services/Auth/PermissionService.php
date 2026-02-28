<?php

namespace App\Services\Auth;

use CodeIgniter\Cache\CacheInterface;

class PermissionService
{
  protected CacheInterface $cache;
  protected array $permissions = [];
  protected bool $isSuperAdmin = false;
  protected bool $booted = false;
  protected int $userId = 0;

  /**
   * Cache TTL in seconds (1 minute — keeps DB reads low while ensuring role changes take effect quickly)
   */
  protected int $cacheTtl = 60;

  public function __construct(?CacheInterface $cache = null)
  {
    $this->cache = $cache ?? \Config\Services::cache();
  }

    // ─────────────────────────────────────────────────────────
    // BOOT — Load permissions once per request
    // ─────────────────────────────────────────────────────────

  /**
   * Boot the permission service for a specific user.
   * Loads permissions from cache or DB. Call once per request.
   *
   * @param int $userId
   * @return void
   */
  public function boot(int $userId): void
  {
    // Prevent double-booting
    if ($this->booted && $this->userId === $userId) {
      return;
    }

    $this->userId = $userId;
    $cacheKey = "user_perms_{$userId}";

    // Try cache first
    $cached = $this->cache->get($cacheKey);
    if ($cached !== null && is_array($cached)) {
      $this->permissions  = $cached['permissions'] ?? [];
      $this->isSuperAdmin = $cached['is_super_admin'] ?? false;
      $this->booted       = true;
      return;
    }

    // Cache miss — load from DB
    $db = \Config\Database::connect();

    $roles = $db->table('user_roles ur')
      ->select('r.permissions')
      ->join('roles r', 'r.id = ur.role_id')
      ->where('ur.user_id', $userId)
      ->where('r.is_active', 1)
      ->get()
      ->getResultArray();

    $merged = [];
    foreach ($roles as $role) {
      $perms = json_decode($role['permissions'], true);
      if (is_array($perms)) {
        $merged = array_merge($merged, $perms);
      }
    }

    $this->permissions = array_values(array_unique($merged));
    $this->isSuperAdmin = in_array('*', $this->permissions);

    // Cache the result
    $this->cache->save($cacheKey, [
      'permissions'   => $this->permissions,
      'is_super_admin' => $this->isSuperAdmin,
    ], $this->cacheTtl);

    $this->booted = true;
  }

    // ─────────────────────────────────────────────────────────
    // CORE PERMISSION CHECKS
    // ─────────────────────────────────────────────────────────

  /**
   * Check if the user has a specific permission.
   * Supports 3-level wildcard cascade:
   *   invoices.account.print → invoices.account.* → invoices.* → *
   *
   * @param string $permission e.g. "invoices.account.print"
   * @return bool
   */
  public function can(string $permission): bool
  {
    if (!$this->booted) {
      // Auto-boot from session if possible
      $session = \Config\Services::session();
      if ($session->has('user_id')) {
        $this->boot((int)$session->get('user_id'));
      } else {
        return false;
      }
    }

    // Super Admin bypass
    if ($this->isSuperAdmin) {
      return true;
    }

    // 1. Exact match
    if (in_array($permission, $this->permissions)) {
      return true;
    }

    // 2. Collapse ".all." segment — e.g. masters.gold_rates.all.list → masters.gold_rates.list
    //    Some modules use 3-segment permissions (masters.gold_rates.list) while controllers
    //    may request the 4-segment form (masters.gold_rates.all.list). This handles both.
    $collapsed = str_replace('.all.', '.', $permission);
    if ($collapsed !== $permission && in_array($collapsed, $this->permissions)) {
      return true;
    }

    // 3. Wildcard cascade
    $parts = explode('.', $permission);

    // Check: module.sub_module.* (e.g. invoices.account.*)
    if (count($parts) >= 3) {
      $subWildcard = $parts[0] . '.' . $parts[1] . '.*';
      if (in_array($subWildcard, $this->permissions)) {
        return true;
      }
    }

    // Check: module.* (e.g. invoices.*)
    if (count($parts) >= 2) {
      $moduleWildcard = $parts[0] . '.*';
      if (in_array($moduleWildcard, $this->permissions)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Check if the user has ANY permission starting with the given prefix.
   * Also resolves wildcards — a user with "invoices.*" satisfies canAny("invoices.account").
   *
   * @param string $prefix e.g. "invoices", "invoices.account", "masters"
   * @return bool
   */
  public function canAny(string $prefix): bool
  {
    if ($this->isSuperAdmin) {
      return true;
    }

    // Direct prefix match
    foreach ($this->permissions as $perm) {
      if (str_starts_with($perm, $prefix . '.') || $perm === $prefix . '.*' || $perm === $prefix) {
        return true;
      }
    }

    // Wildcard resolution: "invoices.*" satisfies canAny("invoices.account")
    $parts = explode('.', $prefix);
    if (count($parts) >= 2) {
      $moduleWildcard = $parts[0] . '.*';
      if (in_array($moduleWildcard, $this->permissions)) {
        return true;
      }
    }

    return false;
  }

  // ─────────────────────────────────────────────────────────
  // SHORTCUT METHODS
  // ─────────────────────────────────────────────────────────

  public function canList(string $module, string $subModule = 'all'): bool
  {
    return $this->can("{$module}.{$subModule}.list");
  }

  public function canCreate(string $module, string $subModule = 'all'): bool
  {
    return $this->can("{$module}.{$subModule}.create");
  }

  public function canEdit(string $module, string $subModule = 'all'): bool
  {
    return $this->can("{$module}.{$subModule}.edit");
  }

  public function canView(string $module, string $subModule = 'all'): bool
  {
    return $this->can("{$module}.{$subModule}.view");
  }

  public function canDelete(string $module, string $subModule = 'all'): bool
  {
    return $this->can("{$module}.{$subModule}.delete");
  }

  public function canPrint(string $module, string $subModule = 'all'): bool
  {
    return $this->can("{$module}.{$subModule}.print");
  }

  public function canChangeStatus(string $module, string $subModule = 'all'): bool
  {
    return $this->can("{$module}.{$subModule}.status_change");
  }

  public function canRecordPayment(string $module, string $subModule = 'all'): bool
  {
    return $this->can("{$module}.{$subModule}.record_payment");
  }

    // ─────────────────────────────────────────────────────────
    // ACTION FLAGS — for passing to views
    // ─────────────────────────────────────────────────────────

  /**
   * Returns an associative array of boolean action flags.
   * Checks both the specific sub_module AND the "all" sub_module (either passing = true).
   *
   * Keys use short names matching the view convention:
   *   $action_flags['create'], $action_flags['edit'], etc.
   *
   * @param string $module    e.g. "invoices", "challans"
   * @param string $subModule e.g. "account", "rhodium", "all"
   * @return array<string, bool>
   */
  public function getActionFlags(string $module, string $subModule): array
  {
    return [
      'list'           => $this->canList($module, $subModule)    || ($subModule !== 'all' && $this->canList($module, 'all')),
      'view'           => $this->canView($module, $subModule)    || ($subModule !== 'all' && $this->canView($module, 'all')),
      'create'         => $this->canCreate($module, $subModule)  || ($subModule !== 'all' && $this->canCreate($module, 'all')),
      'edit'           => $this->canEdit($module, $subModule)    || ($subModule !== 'all' && $this->canEdit($module, 'all')),
      'delete'         => $this->canDelete($module, $subModule)  || ($subModule !== 'all' && $this->canDelete($module, 'all')),
      'print'          => $this->canPrint($module, $subModule)   || ($subModule !== 'all' && $this->canPrint($module, 'all')),
      'status_change'  => $this->canChangeStatus($module, $subModule)   || ($subModule !== 'all' && $this->canChangeStatus($module, 'all')),
      'record_payment' => $this->canRecordPayment($module, $subModule)  || ($subModule !== 'all' && $this->canRecordPayment($module, 'all')),
    ];
  }

    // ─────────────────────────────────────────────────────────
    // MENU BUILDER METHODS — filtered by permissions
    // ─────────────────────────────────────────────────────────

  /**
   * Get invoice menu items the user has access to.
   *
   * @return array
   */
  public function getInvoiceMenuItems(): array
  {
    $items = [];

    // "Invoice List" shows if user has ANY invoice type list permission
    $canListAny = $this->can('invoices.all.list')
      || $this->can('invoices.account.list')
      || $this->can('invoices.cash.list')
      || $this->can('invoices.wax.list');

    if ($canListAny) {
      $items[] = [
        'label'      => 'Invoice List',
        'url'        => 'invoices',
        'sub_module' => 'all',
      ];
    }

    // Create links per type
    $createItems = [
      'account' => ['label' => 'Create Account Invoice', 'url' => 'account-invoices/create'],
      'cash'    => ['label' => 'Create Cash Invoice',    'url' => 'cash-invoices/create'],
      'wax'     => ['label' => 'Create Wax Invoice',     'url' => 'wax-invoices/create'],
    ];

    foreach ($createItems as $sub => $meta) {
      if ($this->canCreate('invoices', $sub) || $this->canCreate('invoices', 'all')) {
        $items[] = [
          'label'      => $meta['label'],
          'url'        => $meta['url'],
          'sub_module' => $sub,
        ];
      }
    }

    return $items;
  }

  /**
   * Get challan menu items the user has access to.
   *
   * @return array
   */
  public function getChallanMenuItems(): array
  {
    $items = [];

    // "Challan List" shows if user has ANY challan type list permission
    $canListAny = $this->can('challans.all.list')
      || $this->can('challans.rhodium.list')
      || $this->can('challans.meena.list')
      || $this->can('challans.wax.list');

    if ($canListAny) {
      $items[] = [
        'label'      => 'Challan List',
        'url'        => 'challans',
        'sub_module' => 'all',
      ];
    }

    // Create links per type
    $createItems = [
      'rhodium' => ['label' => 'Create Rhodium Challan', 'url' => 'challans/create?type=Rhodium'],
      'meena'   => ['label' => 'Create Meena Challan',   'url' => 'challans/create?type=Meena'],
      'wax'     => ['label' => 'Create Wax Challan',     'url' => 'challans/create?type=Wax'],
    ];

    foreach ($createItems as $sub => $meta) {
      if ($this->canCreate('challans', $sub) || $this->canCreate('challans', 'all')) {
        $items[] = [
          'label'      => $meta['label'],
          'url'        => $meta['url'],
          'sub_module' => $sub,
        ];
      }
    }

    return $items;
  }

  /**
   * Get master module menu items the user has access to.
   *
   * @return array
   */
  public function getMasterMenuItems(): array
  {
    $items = [];

    $subModules = [
      'gold_rates'         => ['label' => 'Gold Rates',          'url' => 'masters/gold-rates'],
      'product_categories' => ['label' => 'Product Categories',  'url' => 'masters/product-categories'],
      'products'           => ['label' => 'Products',            'url' => 'masters/products'],
      'processes'          => ['label' => 'Processes',           'url' => 'masters/processes'],
    ];

    foreach ($subModules as $sub => $meta) {
      if ($this->canAny("masters.{$sub}")) {
        $items[] = [
          'label'      => $meta['label'],
          'url'        => $meta['url'],
          'sub_module' => $sub,
        ];
      }
    }

    return $items;
  }

  /**
   * Get customer module menu items the user has access to.
   *
   * @return array
   */
  public function getCustomerMenuItems(): array
  {
    $items = [];

    $subModules = [
      'accounts'       => ['label' => 'Account Customers', 'url' => 'customers/accounts'],
      'cash_customers' => ['label' => 'Cash Customers',    'url' => 'customers/cash-customers'],
    ];

    foreach ($subModules as $sub => $meta) {
      if ($this->canAny("customers.{$sub}")) {
        $items[] = [
          'label'      => $meta['label'],
          'url'        => $meta['url'],
          'sub_module' => $sub,
        ];
      }
    }

    return $items;
  }

  /**
   * Get report menu items the user has access to.
   *
   * @return array
   */
  public function getReportMenuItems(): array
  {
    $items = [];

    $subModules = [
      'daily'       => ['label' => 'Daily Report',         'url' => 'reports/daily'],
      'receivables' => ['label' => 'Ledger Summary',      'url' => 'reports/receivables'],
      'outstanding' => ['label' => 'Outstanding Invoices', 'url' => 'reports/outstanding'],
      'aging'       => ['label' => 'Aging Report',         'url' => 'reports/outstanding/aging'],
      'monthly'     => ['label' => 'Monthly Receivables',  'url' => 'reports/receivables/monthly'],
    ];

    foreach ($subModules as $sub => $meta) {
      if ($this->canAny("reports.{$sub}")) {
        $items[] = [
          'label'      => $meta['label'],
          'url'        => $meta['url'],
          'sub_module' => $sub,
        ];
      }
    }

    return $items;
  }

  /**
   * Get ledger menu items the user has access to.
   *
   * @return array
   */
  public function getLedgerMenuItems(): array
  {
    $items = [];

    $subModules = [
      'accounts'       => ['label' => 'Account Ledgers',    'url' => 'ledgers/accounts'],
      'cash_customers' => ['label' => 'Cash Customer Ledgers', 'url' => 'ledgers/cash-customers'],
      'reminders'      => ['label' => 'Reminders',           'url' => 'ledgers/reminders/outstanding'],
    ];

    foreach ($subModules as $sub => $meta) {
      if ($this->canAny("ledgers.{$sub}")) {
        $items[] = [
          'label'      => $meta['label'],
          'url'        => $meta['url'],
          'sub_module' => $sub,
        ];
      }
    }

    return $items;
  }

    // ─────────────────────────────────────────────────────────
    // TYPE RESOLUTION — DB values → sub_module keys
    // ─────────────────────────────────────────────────────────

  /**
   * Translate invoice_type DB value to sub_module key.
   *
   * @param string $invoiceType e.g. "Accounts Invoice", "Cash Invoice", "Wax Invoice"
   * @return string e.g. "account", "cash", "wax"
   */
  public function resolveInvoiceSubModule(string $invoiceType): string
  {
    $map = [
      'Accounts Invoice' => 'account',
      'Account Invoice'  => 'account',
      'Cash Invoice'     => 'cash',
      'Wax Invoice'      => 'wax',
    ];

    return $map[$invoiceType] ?? 'all';
  }

  /**
   * Translate challan_type DB value to sub_module key.
   *
   * @param string $challanType e.g. "Rhodium", "Meena", "Wax"
   * @return string e.g. "rhodium", "meena", "wax"
   */
  public function resolveChallanSubModule(string $challanType): string
  {
    return strtolower($challanType);
  }

    // ─────────────────────────────────────────────────────────
    // CACHE MANAGEMENT
    // ─────────────────────────────────────────────────────────

  /**
   * Flush cached permissions for a specific user.
   *
   * @param int $userId
   * @return void
   */
  public function flushUser(int $userId): void
  {
    $this->cache->delete("user_perms_{$userId}");

    // If this is the currently booted user, reset state
    if ($this->userId === $userId) {
      $this->booted      = false;
      $this->permissions  = [];
      $this->isSuperAdmin = false;
    }
  }

  /**
   * Flush all cached permissions (all users).
   * Uses cache clean which clears all cache items.
   * For a more targeted approach, you'd need to track user IDs separately.
   *
   * @return void
   */
  public function flushAll(): void
  {
    // Clear all user permission cache keys using known user IDs
    $db = \Config\Database::connect();
    $users = $db->table('users')
      ->select('id')
      ->where('is_deleted', 0)
      ->get()
      ->getResultArray();

    foreach ($users as $user) {
      $this->cache->delete("user_perms_{$user['id']}");
    }

    // Reset current state
    $this->booted      = false;
    $this->permissions  = [];
    $this->isSuperAdmin = false;
  }

    // ─────────────────────────────────────────────────────────
    // GETTERS
    // ─────────────────────────────────────────────────────────

  /**
   * Get all loaded permission strings for the current user.
   *
   * @return array
   */
  public function getPermissions(): array
  {
    return $this->permissions;
  }

  /**
   * Check if the current user is a super admin.
   *
   * @return bool
   */
  public function isSuperAdmin(): bool
  {
    return $this->isSuperAdmin;
  }
}
