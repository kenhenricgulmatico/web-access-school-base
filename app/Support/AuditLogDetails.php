<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\RequestType;
use App\Models\ResourceType;
use App\Models\User;
use Carbon\Carbon;

/**
 * Turns an audit log's raw saved record (JSON) into a readable list for the
 * "View Details" pop-up on the Admin Audit Logs page:
 *
 *   [ 'title' => 'Updated a request', 'rows' => [ ['label' => 'Status', 'value' => 'Approved'], ... ] ]
 *
 * Ids are turned into names (department, requester, request type…) and
 * technical fields (ids, created/updated timestamps, passwords…) are hidden.
 */
class AuditLogDetails
{
    /** Friendly names for the tables that get logged. */
    protected const TABLES = [
        'requests'      => 'request',
        'request_items' => 'request item',
        'users'         => 'user account',
        'sessions'      => 'session',
        'resources'     => 'resource',
        'departments'   => 'department',
        'roles'         => 'role',
    ];

    /** Fields never shown. */
    protected const HIDDEN = [
        'id', 'created_at', 'updated_at', 'deleted_at', 'email_verified_at', 'avatar',
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
        'guard_name', 'pivot', 'request_id', 'resource_id', 'resource',
        'responsibility_center_id', 'current_responsibility_center_id', 'reminder_sent_at',
        'user', 'permissions',
    ];

    /** Nicer labels; anything not listed becomes "Some field". */
    protected const LABELS = [
        'name'                      => 'Name',
        'email'                     => 'Email',
        'status'                    => 'Status',
        'ip'                        => 'IP address',
        'at'                        => 'Time',
        'user_id'                   => 'Requested by',
        'department_id'             => 'Department',
        'request_type_id'           => 'Request type',
        'resource_type_id'          => 'Resource type',
        'purpose'                   => 'Purpose',
        'forwarded_from_request_id' => 'Forwarded from a faculty request',
        'item_name'                 => 'Item / facility',
        'quantity'                  => 'Quantity',
        'request_date'              => 'Date needed',
        'start_time'                => 'Start time',
        'end_time'                  => 'End time',
        'due_back_at'               => 'Return by',
        'returned_at'               => 'Returned on',
        'return_requested_at'       => 'Return requested on',
        'return_condition'          => 'Return condition',
        'return_notes'              => 'Return notes',
        'received_by'               => 'Received by',
        'items'                     => 'Items',
        'roles'                     => 'Role',
        'resource_name'             => 'Resource',
        'quantity_available'        => 'Available',
        'is_returnable'             => 'Must be returned',
        'unit'                      => 'Unit',
        'description'               => 'Description',
        'department_name'           => 'Department name',
    ];

    protected const TIME_FIELDS = ['start_time', 'end_time'];

    protected static array $departments = [];
    protected static array $requestTypes = [];
    protected static array $resourceTypes = [];
    protected static array $users = [];

    public static function for(AuditLog $log): array
    {
        return [
            'title' => static::title($log),
            'rows'  => static::rows(static::record($log), $log->table_name),
        ];
    }

    public static function title(AuditLog $log): string
    {
        $action = (string) $log->action;
        $thing  = static::TABLES[$log->table_name] ?? str_replace('_', ' ', (string) $log->table_name);

        return match ($action) {
            'login'  => 'Logged in',
            'logout' => 'Logged out',
            default  => ucfirst($action) . ' a ' . $thing,
        };
    }

    /** The saved record as an array, whatever shape it was stored in. */
    protected static function record(AuditLog $log): array
    {
        $record = $log->record;

        // Some logs were JSON-encoded twice → still a string after the cast
        if (is_string($record)) {
            $record = json_decode($record, true);
        }
        if (! is_array($record)) {
            return [];
        }

        // Older format: ['id' => .., 'data' => [...]]
        if (isset($record['data']) && is_array($record['data'])) {
            $record = $record['data'] + $record;
            unset($record['data']);
        }

        return $record;
    }

    protected static function rows(array $record, ?string $table): array
    {
        $rows = [];

        foreach ($record as $key => $value) {
            if (in_array($key, static::HIDDEN, true)) {
                continue;
            }

            $text = static::value($key, $value, $table);
            if ($text === null || $text === '') {
                continue;
            }

            $label = static::LABELS[$key] ?? ucfirst(str_replace('_', ' ', (string) $key));

            // "Requested by" only fits requests; elsewhere it's just the user
            if ($key === 'user_id' && $table !== 'requests') {
                $label = 'User';
            }

            $rows[] = ['label' => $label, 'value' => $text];
        }

        return $rows;
    }

    protected static function value(string $key, mixed $value, ?string $table): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        return match (true) {
            $key === 'department_id'             => static::departmentName($value),
            $key === 'request_type_id'           => static::requestTypeName($value),
            $key === 'resource_type_id'          => static::resourceTypeName($value),
            $key === 'user_id', $key === 'received_by' => static::userName($value),
            $key === 'forwarded_from_request_id' => 'Yes',
            $key === 'items'                     => static::items($value),
            $key === 'roles'                     => static::roles($value),
            $key === 'status', $key === 'return_condition' => is_string($value) ? ucwords(str_replace('_', ' ', $value)) : null,
            in_array($key, static::TIME_FIELDS, true) => static::time($value),
            is_bool($value) || $key === 'is_returnable' => $value ? 'Yes' : 'No',
            is_array($value)                     => null, // other nested data isn't useful to read
            static::looksLikeDate($key, $value)  => static::date($value),
            default                              => (string) $value,
        };
    }

    // ---------------------------------------------------------------- lookups

    protected static function departmentName($id): string
    {
        static::$departments = static::$departments ?: Department::pluck('department_name', 'id')->all();

        return static::$departments[(int) $id] ?? 'Unknown department';
    }

    protected static function requestTypeName($id): string
    {
        static::$requestTypes = static::$requestTypes ?: RequestType::pluck('type_name', 'id')->all();

        return static::$requestTypes[(int) $id] ?? 'Unknown type';
    }

    protected static function resourceTypeName($id): string
    {
        static::$resourceTypes = static::$resourceTypes ?: ResourceType::pluck('type_name', 'id')->all();

        return static::$resourceTypes[(int) $id] ?? 'Unknown type';
    }

    protected static function userName($id): string
    {
        $id = (int) $id;
        if (! array_key_exists($id, static::$users)) {
            static::$users[$id] = User::whereKey($id)->value('name');
        }

        return static::$users[$id] ?? 'Deleted user';
    }

    // ---------------------------------------------------------------- formatting

    /** "microphone ×1 (Nov 26, 2026)", one per line. */
    protected static function items($items): ?string
    {
        if (! is_array($items)) {
            return null;
        }

        return collect($items)
            ->filter(fn ($i) => is_array($i) && ! empty($i['item_name']))
            ->map(function ($i) {
                $line = $i['item_name'];
                if (! empty($i['resource_id']) && ! empty($i['quantity'])) {
                    $line .= ' ×' . $i['quantity'];
                }
                if (! empty($i['request_date'])) {
                    $line .= ' (' . static::date($i['request_date'], false);
                    if (! empty($i['start_time']) && ! empty($i['end_time'])) {
                        $line .= ', ' . static::time($i['start_time']) . ' – ' . static::time($i['end_time']);
                    }
                    $line .= ')';
                }
                return $line;
            })
            ->implode("\n") ?: null;
    }

    protected static function roles($roles): ?string
    {
        if (! is_array($roles)) {
            return null;
        }

        return collect($roles)->pluck('name')->filter()->map(fn ($n) => ucwords($n))->implode(', ') ?: null;
    }

    protected static function looksLikeDate(string $key, $value): bool
    {
        return is_string($value)
            && ($key === 'at' || str_ends_with($key, '_at') || str_ends_with($key, '_date'))
            && strtotime($value) !== false;
    }

    protected static function date($value, ?bool $withTime = null): string
    {
        $d = Carbon::parse($value);

        // Show the time only when there is one (a plain date is saved as midnight)
        $withTime ??= $d->format('H:i:s') !== '00:00:00';

        return $withTime
            ? $d->timezone(config('app.timezone'))->format('M d, Y g:i A')
            : $d->format('M d, Y');
    }

    protected static function time($value): ?string
    {
        return rescue(fn () => Carbon::parse($value)->format('g:i A'), (string) $value, false);
    }
}
