<?php

namespace App\Models;

use Database\Factories\BillTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'amount', 'type', 'is_discountable', 'description'])]
class BillType extends Model
{
    /** @use HasFactory<BillTypeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_discountable' => 'boolean',
        ];
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(BillAssignment::class);
    }

    public function studentBills(): HasMany
    {
        return $this->hasMany(StudentBill::class);
    }
}
