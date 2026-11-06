<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_admin_can_download_student_template(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('management.students.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=template_murid.csv');
    }

    public function test_admin_can_import_students_from_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $classRoom = ClassRoom::factory()->create(['name' => 'X RPL 1']);

        $csvContent = "nis,nisn,nama,jenis_kelamin,rombel,kategori,no_hp,nama_wali,no_hp_wali,alamat,status\n";
        $csvContent .= "99001,0099001,Siswa Import 1,L,X RPL 1,reguler,08111,Wali 1,08222,Magelang,aktif\n";
        $csvContent .= "99002,0099002,Siswa Import 2,P,X RPL 1,beasiswa_100,08333,Wali 2,08444,Mungkid,aktif\n";

        $file = UploadedFile::fake()->createWithContent('murid.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('management.students.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('management.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nis' => '99001',
            'name' => 'Siswa Import 1',
            'gender' => 'L',
            'class_room_id' => $classRoom->id,
            'category' => 'reguler',
            'status' => 'aktif',
        ]);

        $this->assertDatabaseHas('students', [
            'nis' => '99002',
            'name' => 'Siswa Import 2',
            'gender' => 'P',
            'class_room_id' => $classRoom->id,
            'category' => 'beasiswa_100',
            'status' => 'aktif',
        ]);
    }
}
