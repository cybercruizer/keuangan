<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_management(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Utama']);

        $response = $this->actingAs($admin)->get(route('management.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Admin Utama');
    }

    public function test_admin_can_create_teller_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('management.users.store'), [
            'name' => 'Teller Baru',
            'username' => 'teller2',
            'email' => 'teller2@smkmuhmungkid.sch.id',
            'password' => 'password123',
            'role' => 'teller',
            'phone' => '081234567890',
        ]);

        $response->assertRedirect(route('management.users.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'teller2',
            'role' => 'teller',
        ]);
    }

    public function test_admin_can_create_murid_user_linked_to_student(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create(['nis' => '10888']);

        $response = $this->actingAs($admin)->post(route('management.users.store'), [
            'name' => 'Murid User Baru',
            'username' => '10888',
            'email' => 'murid10888@student.smkmuhmungkid.sch.id',
            'password' => 'password123',
            'role' => 'murid',
            'student_id' => $student->id,
        ]);

        $response->assertRedirect(route('management.users.index'));
        $this->assertDatabaseHas('users', [
            'username' => '10888',
            'role' => 'murid',
            'student_id' => $student->id,
        ]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete(route('management.users.destroy', $admin));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_teller_cannot_access_user_management(): void
    {
        $teller = User::factory()->teller()->create();

        $response = $this->actingAs($teller)->get(route('management.users.index'));

        $response->assertStatus(403);
    }
}
