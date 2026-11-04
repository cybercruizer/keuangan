<?php

namespace Tests\Feature;

use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MajorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_majors_index(): void
    {
        $admin = User::factory()->admin()->create();
        Major::factory()->create(['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak']);

        $response = $this->actingAs($admin)->get(route('management.majors.index'));

        $response->assertStatus(200);
        $response->assertSee('RPL');
        $response->assertSee('Rekayasa Perangkat Lunak');
    }

    public function test_admin_can_create_major(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('management.majors.store'), [
            'code' => 'TKRO',
            'name' => 'Teknik Kendaraan Ringan Otomotif',
            'description' => 'Program Keahlian Otomotif',
        ]);

        $response->assertRedirect(route('management.majors.index'));
        $this->assertDatabaseHas('majors', [
            'code' => 'TKRO',
            'name' => 'Teknik Kendaraan Ringan Otomotif',
        ]);
    }

    public function test_admin_can_update_major(): void
    {
        $admin = User::factory()->admin()->create();
        $major = Major::factory()->create(['code' => 'TBSM', 'name' => 'Teknik Sepeda Motor']);

        $response = $this->actingAs($admin)->put(route('management.majors.update', $major), [
            'code' => 'TBSM',
            'name' => 'Teknik Bisnis Sepeda Motor',
            'description' => 'Nama baru yang disesuaikan',
        ]);

        $response->assertRedirect(route('management.majors.index'));
        $this->assertDatabaseHas('majors', [
            'id' => $major->id,
            'name' => 'Teknik Bisnis Sepeda Motor',
        ]);
    }

    public function test_admin_can_delete_major_without_classes(): void
    {
        $admin = User::factory()->admin()->create();
        $major = Major::factory()->create(['code' => 'DKV', 'name' => 'Desain Komunikasi Visual']);

        $response = $this->actingAs($admin)->delete(route('management.majors.destroy', $major));

        $response->assertRedirect(route('management.majors.index'));
        $this->assertDatabaseMissing('majors', ['id' => $major->id]);
    }
}
