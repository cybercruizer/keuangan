<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ClassRoomManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_class_rooms(): void
    {
        $admin = User::factory()->admin()->create();
        $classRoom = ClassRoom::factory()->create(['name' => 'X RPL 1']);

        $response = $this->actingAs($admin)->get(route('management.class-rooms.index'));

        $response->assertStatus(200);
        $response->assertSee('X RPL 1');
    }

    public function test_admin_can_create_class_room(): void
    {
        $admin = User::factory()->admin()->create();
        $ay = AcademicYear::factory()->create();
        $major = Major::factory()->create(['code' => 'RPL']);

        $response = $this->actingAs($admin)->post(route('management.class-rooms.store'), [
            'academic_year_id' => $ay->id,
            'major_id' => $major->id,
            'name' => 'X RPL 2',
            'level' => 'X',
        ]);

        $response->assertRedirect(route('management.class-rooms.index'));
        $this->assertDatabaseHas('class_rooms', [
            'name' => 'X RPL 2',
            'level' => 'X',
            'academic_year_id' => $ay->id,
            'major_id' => $major->id,
        ]);
    }

    public function test_admin_can_update_class_room(): void
    {
        $admin = User::factory()->admin()->create();
        $classRoom = ClassRoom::factory()->create(['name' => 'X RPL 1']);

        $response = $this->actingAs($admin)->put(route('management.class-rooms.update', $classRoom), [
            'academic_year_id' => $classRoom->academic_year_id,
            'major_id' => $classRoom->major_id,
            'name' => 'X RPL 1 Unggulan',
            'level' => $classRoom->level,
        ]);

        $response->assertRedirect(route('management.class-rooms.index'));
        $this->assertDatabaseHas('class_rooms', [
            'id' => $classRoom->id,
            'name' => 'X RPL 1 Unggulan',
        ]);
    }

    public function test_admin_can_download_class_room_template(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('management.class-rooms.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=template_rombel.csv');
    }

    public function test_admin_can_import_class_rooms_from_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $ay = AcademicYear::factory()->create(['name' => '2025/2026', 'is_active' => true]);
        $major = Major::factory()->create(['code' => 'RPL']);

        $csvContent = "nama,tingkat,kode_jurusan,tahun_ajaran\n";
        $csvContent .= "X RPL 1,X,RPL,2025/2026\n";
        $csvContent .= "XI RPL 1,XI,RPL,\n";

        $file = UploadedFile::fake()->createWithContent('rombel.csv', $csvContent);

        $response = $this->actingAs($admin)->post(route('management.class-rooms.import'), [
            'file' => $file,
            'academic_year_id' => $ay->id,
        ]);

        $response->assertRedirect(route('management.class-rooms.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('class_rooms', [
            'name' => 'X RPL 1',
            'level' => 'X',
            'major_id' => $major->id,
            'academic_year_id' => $ay->id,
        ]);

        $this->assertDatabaseHas('class_rooms', [
            'name' => 'XI RPL 1',
            'level' => 'XI',
            'major_id' => $major->id,
            'academic_year_id' => $ay->id,
        ]);
    }
}
