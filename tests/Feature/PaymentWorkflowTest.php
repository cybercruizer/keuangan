<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\BillType;
use App\Models\ClassRoom;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teller_can_process_full_payment(): void
    {
        $teller = User::factory()->teller()->create();
        $academicYear = AcademicYear::factory()->create();
        $classRoom = ClassRoom::factory()->create(['academic_year_id' => $academicYear->id]);
        $student = Student::factory()->create(['class_room_id' => $classRoom->id]);
        $billType = BillType::factory()->create(['name' => 'SPP', 'amount' => 250000]);

        $bill = StudentBill::create([
            'student_id' => $student->id,
            'bill_type_id' => $billType->id,
            'academic_year_id' => $academicYear->id,
            'month' => 7,
            'year' => 2026,
            'amount' => 250000,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'final_amount' => 250000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($teller)->post(route('payments.store'), [
            'student_id' => $student->id,
            'payment_date' => '2026-11-04',
            'payment_method' => 'Tunai',
            'bills' => [
                [
                    'selected' => '1',
                    'bill_id' => $bill->id,
                    'pay_amount' => 250000,
                ],
            ],
            'notes' => 'Pembayaran lunas SPP Juli',
        ]);

        $payment = Payment::latest('id')->first();
        $response->assertRedirect(route('payments.receipt', $payment));

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'student_id' => $student->id,
            'user_id' => $teller->id,
            'total_amount' => 250000,
        ]);

        $this->assertDatabaseHas('student_bills', [
            'id' => $bill->id,
            'paid_amount' => 250000,
            'status' => 'paid',
        ]);

        // Assert CashFlow created
        $this->assertDatabaseHas('cash_flows', [
            'payment_id' => $payment->id,
            'type' => 'income',
            'amount' => 250000,
        ]);
    }

    public function test_teller_can_process_partial_payment(): void
    {
        $teller = User::factory()->teller()->create();
        $academicYear = AcademicYear::factory()->create();
        $student = Student::factory()->create();
        $billType = BillType::factory()->create(['name' => 'Uang Gedung', 'type' => 'custom', 'amount' => 1000000]);

        $bill = StudentBill::create([
            'student_id' => $student->id,
            'bill_type_id' => $billType->id,
            'academic_year_id' => $academicYear->id,
            'amount' => 1000000,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'final_amount' => 1000000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        // Cicil 400.000
        $this->actingAs($teller)->post(route('payments.store'), [
            'student_id' => $student->id,
            'payment_date' => '2026-11-04',
            'payment_method' => 'Tunai',
            'bills' => [
                [
                    'selected' => '1',
                    'bill_id' => $bill->id,
                    'pay_amount' => 400000,
                ],
            ],
        ]);

        $this->assertDatabaseHas('student_bills', [
            'id' => $bill->id,
            'paid_amount' => 400000,
            'status' => 'partial',
        ]);
    }

    public function test_receipt_can_be_rendered(): void
    {
        $teller = User::factory()->teller()->create();
        $student = Student::factory()->create(['name' => 'Ahmad Fauzi']);
        $billType = BillType::factory()->create(['name' => 'SPP']);
        $bill = StudentBill::create([
            'student_id' => $student->id,
            'bill_type_id' => $billType->id,
            'academic_year_id' => 1,
            'amount' => 250000,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'final_amount' => 250000,
            'paid_amount' => 250000,
            'status' => 'paid',
        ]);

        $payment = Payment::create([
            'invoice_number' => 'KW-20261104-0001',
            'student_id' => $student->id,
            'user_id' => $teller->id,
            'total_amount' => 250000,
            'payment_method' => 'Tunai',
            'payment_date' => '2026-11-04',
        ]);

        PaymentItem::create([
            'payment_id' => $payment->id,
            'student_bill_id' => $bill->id,
            'amount' => 250000,
            'notes' => 'SPP',
        ]);

        $response = $this->actingAs($teller)->get(route('payments.receipt', $payment));

        $response->assertStatus(200);
        $response->assertSee('KW-20261104-0001');
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('Dua Ratus Lima Puluh Ribu Rupiah');
    }
}
