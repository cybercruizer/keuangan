<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
