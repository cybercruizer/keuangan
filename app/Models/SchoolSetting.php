<?php

namespace App\Models;

use Database\Factories\SchoolSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value'])]
class SchoolSetting extends Model
{
    /** @use HasFactory<SchoolSettingFactory> */
    use HasFactory;

    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, ?string $value): self
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
