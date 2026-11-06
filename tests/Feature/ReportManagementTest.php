<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\BillType;
use App\Models\CashFlow;
use App\Models\ClassRoom;
use App\Models\Major;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_flows_index_can_be_viewed_by_authorized_roles(): void
    {
        $admin = User::factory()->admin()->create();
        $teller = User::factory()->teller()->create();
        $kepsek = User::factory()->kepsek()->create();

        CashFlow::create([
            'date' => '2026-11-06',
            'type' => 'income',
            'category' => 'Pembayaran SPP',
            'amount' => 500000,
            'description' => 'SPP Siswa',
            'user_id' => $teller->id,
        ]);

        $this->actingAs($admin)->get(route('reports.cash-flows.index'))->assertStatus(200)->assertSee('Pembayaran SPP');
        $this->actingAs($teller)->get(route('reports.cash-flows.index'))->assertStatus(200)->assertSee('Pembayaran SPP');
        $this->actingAs($kepsek)->get(route('reports.cash-flows.index'))->assertStatus(200)->assertSee('Pembayaran SPP');
    }

    public function test_cash_flow_can_be_filtered_by_period(): void
    {
        $admin = User::factory()->admin()->create();

        CashFlow::create([
            'date' => '2026-05-10',
            'type' => 'income',
            'category' => 'Infaq',
            'amount' => 100000,
            'description' => 'Infaq Mei',
            'user_id' => $admin->id,
        ]);

        CashFlow::create([
            'date' => '2026-11-06',
            'type' => 'expense',
            'category' => 'Listrik',
            'amount' => 250000,
            'description' => 'PLN November',
            'user_id' => $admin->id,
        ]);

        // Monthly filter November 2026
        $response = $this->actingAs($admin)->get(route('reports.cash-flows.index', [
            'period' => 'monthly',
            'month' => 11,
            'year' => 2026,
        ]));
        $response->assertStatus(200);
        $response->assertSee('PLN November');
        $response->assertDontSee('Infaq Mei');

        // Yearly filter 2026
        $responseYear = $this->actingAs($admin)->get(route('reports.cash-flows.index', [
            'period' => 'yearly',
            'year' => 2026,
        ]));
        $responseYear->assertStatus(200);
        $responseYear->assertSee('PLN November');
        $responseYear->assertSee('Infaq Mei');
    }

    public function test_authorized_user_can_store_manual_cash_flow(): void
    {
        $teller = User::factory()->teller()->create();

        $response = $this->actingAs($teller)->post(route('reports.cash-flows.store'), [
            'date' => '2026-11-06',
            'type' => 'expense',
            'category' => 'ATK',
            'amount' => 150000,
            'description' => 'Beli kertas HVS A4 dan tinta',
        ]);

        $response->assertRedirect(route('reports.cash-flows.index'));
        $this->assertDatabaseHas('cash_flows', [
            'type' => 'expense',
            'category' => 'ATK',
            'amount' => 150000,
            'user_id' => $teller->id,
        ]);
    }

    public function test_cash_flow_print_and_export_work(): void
    {
        $admin = User::factory()->admin()->create();
        CashFlow::create([
            'date' => '2026-11-06',
            'type' => 'income',
            'category' => 'Kasir',
            'amount' => 750000,
            'description' => 'Pembayaran Kasir',
            'user_id' => $admin->id,
        ]);

        // Print view
        $printResponse = $this->actingAs($admin)->get(route('reports.cash-flows.print'));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('LAPORAN ARUS KAS', false);
        $printResponse->assertSee('Pembayaran Kasir');

        // CSV export
        $exportResponse = $this->actingAs($admin)->get(route('reports.cash-flows.index', ['export' => 1]));
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Disposition', 'attachment; filename=laporan_arus_kas.csv');
    }

    public function test_student_recap_can_be_viewed_printed_and_exported(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create(['name' => 'Fulan bin Fulan']);
        $billType = BillType::factory()->create(['name' => 'SPP']);
        StudentBill::create([
            'student_id' => $student->id,
            'bill_type_id' => $billType->id,
            'academic_year_id' => 1,
            'amount' => 200000,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'final_amount' => 200000,
            'paid_amount' => 200000,
            'status' => 'paid',
        ]);

        $indexResponse = $this->actingAs($admin)->get(route('reports.recaps.students'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Fulan bin Fulan');
        $indexResponse->assertSee('100% Lunas');

        // Print
        $printResponse = $this->actingAs($admin)->get(route('reports.recaps.students', ['print' => 1]));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Fulan bin Fulan');

        // Export
        $exportResponse = $this->actingAs($admin)->get(route('reports.recaps.students', ['export' => 1]));
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Disposition', 'attachment; filename=rekap_pembayaran_siswa.csv');
    }

    public function test_class_room_recap_can_be_viewed_printed_and_exported(): void
    {
        $admin = User::factory()->admin()->create();
        $ay = AcademicYear::factory()->create(['name' => '2025/2026', 'is_active' => true]);
        $major = Major::factory()->create(['code' => 'RPL']);
        $classRoom = ClassRoom::factory()->create([
            'name' => 'X RPL 1',
            'academic_year_id' => $ay->id,
            'major_id' => $major->id,
        ]);
        $student = Student::factory()->create(['class_room_id' => $classRoom->id]);
        $billType = BillType::factory()->create();
        StudentBill::create([
            'student_id' => $student->id,
            'bill_type_id' => $billType->id,
            'academic_year_id' => $ay->id,
            'amount' => 300000,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'final_amount' => 300000,
            'paid_amount' => 150000,
            'status' => 'partial',
        ]);

        $indexResponse = $this->actingAs($admin)->get(route('reports.recaps.class-rooms', ['academic_year_id' => $ay->id]));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('X RPL 1');
        $indexResponse->assertSee('50%');

        // Print
        $printResponse = $this->actingAs($admin)->get(route('reports.recaps.class-rooms', [
            'academic_year_id' => $ay->id,
            'print' => 1,
        ]));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('X RPL 1');

        // Export
        $exportResponse = $this->actingAs($admin)->get(route('reports.recaps.class-rooms', [
            'academic_year_id' => $ay->id,
            'export' => 1,
        ]));
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Disposition', 'attachment; filename=rekap_pembayaran_rombel.csv');
    }

    public function test_bill_type_recap_can_be_viewed_printed_and_exported(): void
    {
        $admin = User::factory()->admin()->create();
        $ay = AcademicYear::factory()->create(['name' => '2025/2026', 'is_active' => true]);
        $billType = BillType::factory()->create(['name' => 'Uang Seragam Olahraga', 'amount' => 180000]);
        $student = Student::factory()->create();
        StudentBill::create([
            'student_id' => $student->id,
            'bill_type_id' => $billType->id,
            'academic_year_id' => $ay->id,
            'amount' => 180000,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'final_amount' => 180000,
            'paid_amount' => 180000,
            'status' => 'paid',
        ]);

        $indexResponse = $this->actingAs($admin)->get(route('reports.recaps.bill-types', ['academic_year_id' => $ay->id]));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Uang Seragam Olahraga');
        $indexResponse->assertSee('100%');

        // Print
        $printResponse = $this->actingAs($admin)->get(route('reports.recaps.bill-types', [
            'academic_year_id' => $ay->id,
            'print' => 1,
        ]));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Uang Seragam Olahraga');

        // Export
        $exportResponse = $this->actingAs($admin)->get(route('reports.recaps.bill-types', [
            'academic_year_id' => $ay->id,
            'export' => 1,
        ]));
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Disposition', 'attachment; filename=rekap_pembayaran_tagihan.csv');
    }

    public function test_student_cannot_access_reporting_module(): void
    {
        $studentUser = User::factory()->murid()->create();

        $this->actingAs($studentUser)->get(route('reports.cash-flows.index'))->assertStatus(403);
        $this->actingAs($studentUser)->get(route('reports.recaps.students'))->assertStatus(403);
        $this->actingAs($studentUser)->get(route('reports.recaps.class-rooms'))->assertStatus(403);
        $this->actingAs($studentUser)->get(route('reports.recaps.bill-types'))->assertStatus(403);
    }
}
