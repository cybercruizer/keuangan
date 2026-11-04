<?php

namespace App\Models;

use Database\Factories\MajorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'description'])]
class Major extends Model
{
    /** @use HasFactory<MajorFactory> */
    use HasFactory;

    public function classRooms(): HasMany
    {
        return $this->hasMany(ClassRoom::class);
    }
}
