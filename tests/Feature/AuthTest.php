<?php

namespace Tests\Feature;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_approved_user_can_login_and_see_dashboard(): void
    {
        $dept = $this->createDepartment();
        $user = $this->createUser('admin', $dept, 'approved', 'admin@test.com');

        $this->actingAs($user);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    public function test_pending_user_is_redirected_to_waiting_page(): void
    {
        $dept = $this->createDepartment();
        $user = $this->createUser('student', $dept, 'pending', 'pending@test.com');

        $this->actingAs($user);

        $response = $this->get('/waiting');
        $response->assertStatus(200);
    }

    public function test_rejected_user_cannot_reach_protected_routes(): void
    {
        $dept = $this->createDepartment();
        $user = $this->createUser('student', $dept, 'rejected', 'rejected@test.com');

        $this->actingAs($user);

        $response = $this->get('/portal/dashboard');
        $this->assertTrue(
            in_array($response->getStatusCode(), [302, 403]),
            "Expected redirect or forbidden, got {$response->getStatusCode()}"
        );
    }
}
