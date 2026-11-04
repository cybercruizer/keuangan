<?php

namespace App\Models;

use Database\Factories\StudentBillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'student_id',
    'bill_type_id',
    'academic_year_id',
    'month',
    'year',
    'amount',
    'discount_percentage',
    'discount_amount',
    'final_amount',
    'paid_amount',
    'status',
    'due_date',
])]
class StudentBill extends Model
{
    /** @use HasFactory<StudentBillFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function billType(): BelongsTo
    {
        return $this->belongsTo(BillType::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function paymentItems(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->final_amount - (float) $this->paid_amount);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    public function getMonthNameAttribute(): ?string
    {
        if (! $this->month) {
            return null;
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[$this->month] ?? null;
    }
}
