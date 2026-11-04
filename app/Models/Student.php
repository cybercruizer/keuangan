<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'nis',
    'nisn',
    'name',
    'gender',
    'class_room_id',
    'category',
    'phone',
    'parent_name',
    'parent_phone',
    'address',
    'status',
])]
class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(StudentBill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function savingsTransactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class);
    }

    public function currentSavingsBalance(): float
    {
        $lastTx = $this->savingsTransactions()->latest('id')->first();

        return $lastTx ? (float) $lastTx->balance_after : 0.00;
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'beasiswa_50' => 'Beasiswa 50%',
            'beasiswa_100' => 'Beasiswa 100%',
            default => 'Reguler',
        };
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }
}
