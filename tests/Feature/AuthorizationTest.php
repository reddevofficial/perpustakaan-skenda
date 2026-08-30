<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class AuthorizationTest extends TestCase
{
    use CreatesTestData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBaseTables();
        $this->createRoles();
    }

    public function test_guest_can_access_portal_catalog(): void
    {
        $response = $this->get(route('portal.catalog'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_protected_portal_routes(): void
    {
        $response = $this->get(route('portal.loans'));
        $response->assertRedirect();
    }

    public function test_student_can_access_portal_catalog(): void
    {
        $student = $this->createStudent();
        $studentUser = $this->createStudentUser($student);

        $response = $this->actingAs($studentUser)->get(route('portal.catalog'));
        $response->assertStatus(200);
    }

    public function test_student_can_access_own_portal_loans(): void
    {
        $student = $this->createStudent();
        $studentUser = $this->createStudentUser($student);

        $response = $this->actingAs($studentUser)->get(route('portal.loans'));
        $response->assertStatus(200);
    }

    public function test_student_can_access_own_portal_reservations(): void
    {
        $student = $this->createStudent();
        $studentUser = $this->createStudentUser($student);

        $response = $this->actingAs($studentUser)->get(route('portal.reservations'));
        $response->assertStatus(200);
    }

    public function test_student_can_access_own_portal_profile(): void
    {
        $student = $this->createStudent();
        $studentUser = $this->createStudentUser($student);

        $response = $this->actingAs($studentUser)->get(route('portal.profile'));
        $response->assertStatus(200);
    }

    public function test_student_can_access_own_portal_notifications(): void
    {
        $student = $this->createStudent();
        $studentUser = $this->createStudentUser($student);

        $response = $this->actingAs($studentUser)->get(route('portal.notifications'));
        $response->assertStatus(200);
    }

    public function test_student_user_is_not_staff(): void
    {
        $student = $this->createStudent();
        $studentUser = $this->createStudentUser($student);

        $this->assertTrue($studentUser->isStudent());
        $this->assertFalse($studentUser->isStaff());
        $this->assertFalse($studentUser->isSuperAdmin());
    }

    public function test_staff_user_has_correct_role(): void
    {
        $staff = $this->createStaff();

        $this->assertTrue($staff->isStaff());
        $this->assertFalse($staff->isStudent());
        $this->assertFalse($staff->isSuperAdmin());
    }

    public function test_super_admin_has_correct_role(): void
    {
        $admin = $this->createSuperAdmin();

        $this->assertTrue($admin->isSuperAdmin());
        $this->assertTrue($admin->isStaff());
        $this->assertFalse($admin->isStudent());
    }

    public function test_student_user_has_loan_request_permission(): void
    {
        $student = $this->createStudent();
        $studentUser = $this->createStudentUser($student);

        $this->assertTrue($studentUser->hasPermission('loan-request'));
        $this->assertTrue($studentUser->hasPermission('catalog-view'));
        $this->assertFalse($studentUser->hasPermission('loan-manage'));
    }

    public function test_staff_user_has_loan_manage_permission(): void
    {
        $staff = $this->createStaff();

        $this->assertTrue($staff->hasPermission('loan-manage'));
        $this->assertTrue($staff->hasPermission('loan-approve'));
        $this->assertTrue($staff->hasPermission('return-process'));
    }

    public function test_super_admin_has_all_permissions(): void
    {
        $admin = $this->createSuperAdmin();

        $this->assertTrue($admin->hasPermission('loan-manage'));
        $this->assertTrue($admin->hasPermission('loan-approve'));
    }
}
