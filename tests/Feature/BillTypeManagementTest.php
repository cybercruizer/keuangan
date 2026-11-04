<?php

namespace Tests\Feature;

use App\Models\BillType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_bill_types(): void
    {
        $admin = User::factory()->admin()->create();
        BillType::factory()->create(['name' => 'SPP Bulanan']);

        $response = $this->actingAs($admin)->get(route('billing.bill-types.index'));

        $response->assertStatus(200);
        $response->assertSee('SPP Bulanan');
    }

    public function test_admin_can_create_discountable_bill_type_with_rules(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('billing.bill-types.store'), [
            'name' => 'Uang Gedung Sarpras',
            'amount' => 1500000,
            'type' => 'custom',
            'is_discountable' => '1',
            'description' => 'Iuran Sarpras',
            'discount_50' => 50,
            'discount_100' => 100,
        ]);

        $response->assertRedirect(route('billing.bill-types.index'));
        $this->assertDatabaseHas('bill_types', [
            'name' => 'Uang Gedung Sarpras',
            'amount' => 1500000,
            'is_discountable' => true,
        ]);

        $billType = BillType::where('name', 'Uang Gedung Sarpras')->first();
        $this->assertDatabaseHas('discounts', [
            'bill_type_id' => $billType->id,
            'student_category' => 'beasiswa_50',
            'percentage' => 50,
        ]);
        $this->assertDatabaseHas('discounts', [
            'bill_type_id' => $billType->id,
            'student_category' => 'beasiswa_100',
            'percentage' => 100,
        ]);
    }

    public function test_admin_can_update_bill_type(): void
    {
        $admin = User::factory()->admin()->create();
        $billType = BillType::factory()->create([
            'name' => 'SPP Lama',
            'amount' => 200000,
            'type' => 'spp',
        ]);

        $response = $this->actingAs($admin)->put(route('billing.bill-types.update', $billType), [
            'name' => 'SPP Baru',
            'amount' => 250000,
            'type' => 'spp',
            'is_discountable' => '1',
            'discount_50' => 50,
            'discount_100' => 100,
        ]);

        $response->assertRedirect(route('billing.bill-types.index'));
        $this->assertDatabaseHas('bill_types', [
            'id' => $billType->id,
            'name' => 'SPP Baru',
            'amount' => 250000,
        ]);
    }
}
