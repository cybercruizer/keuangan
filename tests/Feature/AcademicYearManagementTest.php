<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_academic_years_index(): void
    {
        $admin = User::factory()->admin()->create();
        AcademicYear::factory()->create(['name' => '2025/2026', 'semester' => 'ganjil']);

        $response = $this->actingAs($admin)->get(route('management.academic-years.index'));

        $response->assertStatus(200);
        $response->assertSee('2025/2026');
    }

    public function test_admin_can_create_academic_year(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('management.academic-years.store'), [
            'name' => '2026/2027',
            'semester' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('management.academic-years.index'));
        $this->assertDatabaseHas('academic_years', [
            'name' => '2026/2027',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_set_active_academic_year(): void
    {
        $admin = User::factory()->admin()->create();
        $year1 = AcademicYear::factory()->create(['name' => '2024/2025', 'is_active' => true]);
        $year2 = AcademicYear::factory()->create(['name' => '2025/2026', 'is_active' => false]);

        $response = $this->actingAs($admin)->post(route('management.academic-years.set-active', $year2));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('academic_years', ['id' => $year1->id, 'is_active' => false]);
        $this->assertDatabaseHas('academic_years', ['id' => $year2->id, 'is_active' => true]);
    }

    public function test_student_cannot_access_academic_years_management(): void
    {
        $murid = User::factory()->murid()->create();

        $response = $this->actingAs($murid)->get(route('management.academic-years.index'));

        $response->assertStatus(403);
    }
}
