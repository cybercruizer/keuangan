<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_students_list(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->beasiswa50()->create([
            'nis' => '10999',
            'name' => 'Ahmad Test',
        ]);

        $response = $this->actingAs($admin)->get(route('management.students.index'));

        $response->assertStatus(200);
        $response->assertSee('10999');
        $response->assertSee('Ahmad Test');
        $response->assertSee('Beasiswa 50%');
    }

    public function test_admin_can_create_student(): void
    {
        $admin = User::factory()->admin()->create();
        $classRoom = ClassRoom::factory()->create();

        $response = $this->actingAs($admin)->post(route('management.students.store'), [
            'nis' => '20001',
            'nisn' => '0071239999',
            'name' => 'Siswa Baru Beasiswa',
            'gender' => 'L',
            'class_room_id' => $classRoom->id,
            'category' => 'beasiswa_100',
            'phone' => '08123456789',
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('management.students.index'));
        $this->assertDatabaseHas('students', [
            'nis' => '20001',
            'name' => 'Siswa Baru Beasiswa',
            'category' => 'beasiswa_100',
            'class_room_id' => $classRoom->id,
        ]);
    }

    public function test_admin_can_view_student_details(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create(['name' => 'Siswa Detail Test']);

        $response = $this->actingAs($admin)->get(route('management.students.show', $student));

        $response->assertStatus(200);
        $response->assertSee('Siswa Detail Test');
    }

    public function test_admin_can_filter_students_by_category(): void
    {
        $admin = User::factory()->admin()->create();
        Student::factory()->reguler()->create(['name' => 'Siswa Reguler']);
        Student::factory()->beasiswa100()->create(['name' => 'Siswa Beasiswa Penuh']);

        $response = $this->actingAs($admin)->get(route('management.students.index', ['category' => 'beasiswa_100']));

        $response->assertStatus(200);
        $response->assertSee('Siswa Beasiswa Penuh');
        $response->assertDontSee('Siswa Reguler');
    }

    public function test_teller_can_access_students_management(): void
    {
        $teller = User::factory()->teller()->create();

        $response = $this->actingAs($teller)->get(route('management.students.index'));

        $response->assertStatus(200);
    }
}
