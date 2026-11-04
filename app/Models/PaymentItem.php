<?php

namespace App\Models;

use Database\Factories\PaymentItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['payment_id', 'student_bill_id', 'amount', 'notes'])]
class PaymentItem extends Model
{
    /** @use HasFactory<PaymentItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function studentBill(): BelongsTo
    {
        return $this->belongsTo(StudentBill::class);
    }
}
