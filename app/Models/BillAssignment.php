<?php

namespace App\Models;

use Database\Factories\BillAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bill_type_id', 'academic_year_id', 'class_room_id', 'student_category'])]
class BillAssignment extends Model
{
    /** @use HasFactory<BillAssignmentFactory> */
    use HasFactory;

    public function billType(): BelongsTo
    {
        return $this->belongsTo(BillType::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }
}
