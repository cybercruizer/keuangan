<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\BillType;
use App\Models\ClassRoom;
use App\Models\Discount;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentBillManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_batch_generate_spp_with_discounts(): void
    {
        $admin = User::factory()->admin()->create();
        $academicYear = AcademicYear::factory()->create(['is_active' => true]);
        $classRoom = ClassRoom::factory()->create(['academic_year_id' => $academicYear->id]);

        $studentReguler = Student::factory()->reguler()->create(['class_room_id' => $classRoom->id]);
        $student50 = Student::factory()->beasiswa50()->create(['class_room_id' => $classRoom->id]);
        $student100 = Student::factory()->beasiswa100()->create(['class_room_id' => $classRoom->id]);

        $billType = BillType::factory()->create([
            'name' => 'SPP Bulanan',
            'amount' => 200000,
            'type' => 'spp',
            'is_discountable' => true,
        ]);

        Discount::create([
            'bill_type_id' => $billType->id,
            'student_category' => 'beasiswa_50',
            'percentage' => 50,
        ]);
        Discount::create([
            'bill_type_id' => $billType->id,
            'student_category' => 'beasiswa_100',
            'percentage' => 100,
        ]);

        $response = $this->actingAs($admin)->post(route('billing.student-bills.store'), [
            'bill_type_id' => $billType->id,
            'academic_year_id' => $academicYear->id,
            'months' => [7, 8], // Juli & Agustus
            'year' => 2026,
            'class_room_ids' => [$classRoom->id],
            'student_categories' => ['reguler', 'beasiswa_50', 'beasiswa_100'],
        ]);

        $response->assertRedirect(route('billing.student-bills.index'));
        $response->assertSessionHas('success');

        // Check Reguler Student (2 months * 200.000 = 200.000 each, unpaid)
        $this->assertDatabaseHas('student_bills', [
            'student_id' => $studentReguler->id,
            'month' => 7,
            'amount' => 200000,
            'discount_amount' => 0,
            'final_amount' => 200000,
            'status' => 'unpaid',
        ]);

        // Check Beasiswa 50% (200.000 - 100.000 = 100.000)
        $this->assertDatabaseHas('student_bills', [
            'student_id' => $student50->id,
            'month' => 7,
            'amount' => 200000,
            'discount_percentage' => 50,
            'discount_amount' => 100000,
            'final_amount' => 100000,
            'status' => 'unpaid',
        ]);

        // Check Beasiswa 100% (200.000 - 200.000 = 0, paid)
        $this->assertDatabaseHas('student_bills', [
            'student_id' => $student100->id,
            'month' => 7,
            'amount' => 200000,
            'discount_percentage' => 100,
            'discount_amount' => 200000,
            'final_amount' => 0,
            'status' => 'paid',
        ]);
    }

    public function test_batch_generate_avoids_duplicate_bills(): void
    {
        $admin = User::factory()->admin()->create();
        $academicYear = AcademicYear::factory()->create();
        $classRoom = ClassRoom::factory()->create(['academic_year_id' => $academicYear->id]);
        $student = Student::factory()->reguler()->create(['class_room_id' => $classRoom->id]);
        $billType = BillType::factory()->create(['type' => 'spp', 'amount' => 200000]);

        // First generation
        $this->actingAs($admin)->post(route('billing.student-bills.store'), [
            'bill_type_id' => $billType->id,
            'academic_year_id' => $academicYear->id,
            'months' => [7],
            'year' => 2026,
            'class_room_ids' => [$classRoom->id],
            'student_categories' => ['reguler'],
        ]);

        $this->assertEquals(1, StudentBill::where('student_id', $student->id)->count());

        // Second generation (same parameters)
        $this->actingAs($admin)->post(route('billing.student-bills.store'), [
            'bill_type_id' => $billType->id,
            'academic_year_id' => $academicYear->id,
            'months' => [7],
            'year' => 2026,
            'class_room_ids' => [$classRoom->id],
            'student_categories' => ['reguler'],
        ]);

        // Count should still be 1 (skipped duplicate)
        $this->assertEquals(1, StudentBill::where('student_id', $student->id)->count());
    }
}
