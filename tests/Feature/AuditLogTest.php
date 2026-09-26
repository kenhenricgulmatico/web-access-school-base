<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_creating_a_department_writes_an_audit_log(): void
    {
        Department::create(['department_name' => 'Computer Studies']);

        $this->assertDatabaseHas('audit_logs', [
            'action'     => 'created',
            'table_name' => 'departments',
        ]);
    }

    public function test_audit_log_allows_null_user_id_when_not_logged_in(): void
    {
        $this->assertGuest();

        Department::create(['department_name' => 'Engineering']);

        $log = AuditLog::where('table_name', 'departments')->first();

        $this->assertNotNull($log);
        $this->assertNull($log->user_id);
        $this->assertIsArray($log->record);
    }
}
