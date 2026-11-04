<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Major;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_metrics_and_charts(): void
    {
        $admin = User::factory()->admin()->create();
        $academicYear = AcademicYear::factory()->create(['is_active' => true]);
        $major = Major::factory()->create(['code' => 'RPL']);
        $classRoom = ClassRoom::factory()->create([
            'academic_year_id' => $academicYear->id,
            'major_id' => $major->id,
            'name' => 'X RPL 1',
        ]);
        $student = Student::factory()->create(['class_room_id' => $classRoom->id]);

        Payment::create([
            'invoice_number' => 'KW-20261104-0001',
            'student_id' => $student->id,
            'user_id' => $admin->id,
            'total_amount' => 500000,
            'payment_method' => 'Tunai',
            'payment_date' => today(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Keuangan Siswa');
        $response->assertSee('Rp 500.000');
        $response->assertSee('Grafik Tren Pembayaran');
        $response->assertSee('Progress Pembayaran per Rombongan Belajar (Rombel)');
        $response->assertSee('X RPL 1');
    }
}
