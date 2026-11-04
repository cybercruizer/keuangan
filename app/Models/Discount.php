<?php

namespace App\Models;

use Database\Factories\DiscountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bill_type_id', 'student_category', 'percentage'])]
class Discount extends Model
{
    /** @use HasFactory<DiscountFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
        ];
    }

    public function billType(): BelongsTo
    {
        return $this->belongsTo(BillType::class);
    }
}
