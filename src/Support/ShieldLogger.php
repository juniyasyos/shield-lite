<?php

namespace juniyasyos\ShieldLite\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Shield Lite Logger
 *
 * Provides comprehensive logging functionality for Shield Lite operations
 * with context-aware logging and structured data.
 */
class ShieldLogger
{
    private const CHANNEL = 'shield-lite';

    private static array $context = [];

    /**
     * Set global context for all log entries
     */
    public static function setContext(array $context): void
    {
        self::$context = array_merge(self::$context, $context);
    }

    /**
     * Clear global context
     */
    public static function clearContext(): void
    {
        self::$context = [];
    }

    /**
     * Log critical system errors
     */
    public static function critical(string $message, array $context = [], ?\Throwable $exception = null): void
    {
        self::writeLog('critical', $message, $context, $exception);
    }

    /**
     * Log errors that need immediate attention
     */
    public static function error(string $message, array $context = [], ?\Throwable $exception = null): void
    {
        self::writeLog('error', $message, $context, $exception);
    }

    /**
     * Log warnings for potential issues
     */
    public static function warning(string $message, array $context = []): void
    {
        self::writeLog('warning', $message, $context);
    }

    /**
     * Log informational messages
     */
    public static function info(string $message, array $context = []): void
    {
        self::writeLog('info', $message, $context);
    }

    /**
     * Log debug information
     */
    public static function debug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            self::writeLog('debug', $message, $context);
        }
    }

    /**
     * Log permission operations
     */
    public static function permission(string $action, string $permission, array $context = []): void
    {
        self::info("Permission {$action}: {$permission}", array_merge($context, [
            'action' => $action,
            'permission' => $permission,
            'type' => 'permission_operation'
        ]));
    }

    /**
     * Log role operations
     */
    public static function role(string $action, string $role, array $context = []): void
    {
        self::info("Role {$action}: {$role}", array_merge($context, [
            'action' => $action,
            'role' => $role,
            'type' => 'role_operation'
        ]));
    }

    /**
     * Log user operations
     */
    public static function user(string $action, int|string $userId, array $context = []): void
    {
        self::info("User {$action}: {$userId}", array_merge($context, [
            'action' => $action,
            'user_id' => $userId,
            'type' => 'user_operation'
        ]));
    }

    /**
     * Log performance metrics
     */
    public static function performance(string $operation, float $duration, array $context = []): void
    {
        $level = $duration > 1000 ? 'warning' : 'info';

        self::writeLog($level, "Performance: {$operation} took {$duration}ms", array_merge($context, [
            'operation' => $operation,
            'duration_ms' => $duration,
            'type' => 'performance'
        ]));
    }

    /**
     * Log database operations
     */
    public static function database(string $operation, string $table, array $context = []): void
    {
        self::debug("Database {$operation}: {$table}", array_merge($context, [
            'operation' => $operation,
            'table' => $table,
            'type' => 'database_operation'
        ]));
    }

    /**
     * Log validation errors
     */
    public static function validation(string $field, string $error, array $data = []): void
    {
        self::warning("Validation failed for {$field}: {$error}", [
            'field' => $field,
            'error' => $error,
            'data' => $data,
            'type' => 'validation_error'
        ]);
    }

    /**
     * Write log entry with full context
     */
    private static function writeLog(string $level, string $message, array $context = [], ?\Throwable $exception = null): void
    {
        $fullContext = array_merge(
            self::getSystemContext(),
            self::$context,
            $context
        );

        if ($exception) {
            $fullContext['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        Log::channel(self::CHANNEL)->{$level}($message, $fullContext);
    }

    /**
     * Get system context for all log entries
     */
    private static function getSystemContext(): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'user_id' => Auth::id(),
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'session_id' => session()->getId(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ];
    }
}
