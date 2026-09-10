<?php

namespace Tests\Feature;

use App\Enums\BookCopyStatus;
use App\Enums\UserRole;
use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\BookResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\StudentResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_view_administrative_resources(): void
    {
        $student = User::factory()->create(['role' => UserRole::STUDENT]);
        $this->actingAs($student);

        $this->assertFalse(BookResource::canViewAny());
        $this->assertFalse(CategoryResource::canViewAny());
        $this->assertFalse(StudentResource::canViewAny());
        $this->assertFalse(ActivityLogResource::canViewAny());
    }

    public function test_staff_can_view_operational_resources_but_not_activity_log(): void
    {
        $staff = User::factory()->create(['role' => UserRole::STAFF]);
        $this->actingAs($staff);

        $this->assertTrue(BookResource::canViewAny());
        $this->assertTrue(CategoryResource::canViewAny());
        $this->assertTrue(StudentResource::canViewAny());
        $this->assertFalse(ActivityLogResource::canViewAny());
    }

    public function test_reserved_copy_status_is_part_of_the_domain_contract(): void
    {
        $this->assertSame('reserved', BookCopyStatus::RESERVED->value);
    }
}
