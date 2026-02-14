<?php

namespace App\Services\Audit;

use CodeIgniter\I18n\Time;

/**
 * AuditService
 * 
 * Handles logging of critical system events for security and debugging.
 */
class AuditService
{
    /**
     * Log a system event
     *
     * @param string $event Type of event (e.g., 'login', 'logout', 'failed_login')
     * @param string $message Description
     * @param array $context Additional data
     */
    public function log(string $event, string $message, array $context = []): void
    {
        $logPath = WRITEPATH . 'logs/audit-' . date('Y-m-d') . '.log';
        
        $entry = [
            'timestamp' => Time::now()->toDateTimeString(),
            'event'     => $event,
            'message'   => $message,
            'context'   => $context,
            'ip_address' => service('request')->getIPAddress(),
            'user_agent' => service('request')->getUserAgent()->getAgentString()
        ];
        
        $logMessage = json_encode($entry) . PHP_EOL;
        
        // Append to log file
        file_put_contents($logPath, $logMessage, FILE_APPEND);
    }

    /**
     * Log a specific business action (CRUD operations)
     *
     * @param string $action      Action identifier (e.g., 'product.create')
     * @param int|string $recordId   ID of the record affected
     * @param string $recordCode  Code/Reference of the record
     * @param string $message     Description of the action
     */
    public function logAction(string $action, $recordId, string $recordCode, string $message): void
    {
        $userId = session()->get('user_id') ?? 0;
        
        $context = [
            'record_id'   => $recordId,
            'record_code' => $recordCode,
            'user_id'     => $userId
        ];

        $this->log($action, $message, $context);
    }

    /**
     * Log CRUD operation with specific signature matching services
     */
    public function logCrud(string $module, string $action, $recordId, $before = null, $after = null): void
    {
        $message = ucfirst($module) . ' ' . ucfirst($action);
        $context = [
            'record_id' => $recordId,
            'before'    => $before,
            'after'     => $after
        ];
        $this->log($module . '.' . $action, $message, $context);
    }
}
