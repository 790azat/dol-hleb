<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

/** Настройки сайта, редактируемые в админке (ключ → значение). */
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    private static ?array $cache = null;

    public static function get(string $key, $default = null)
    {
        if (self::$cache === null) {
            try {
                self::$cache = self::query()->pluck('value', 'key')->all();
            } catch (Throwable) {
                self::$cache = [];
            }
        }

        $value = self::$cache[$key] ?? null;

        return $value === null || $value === '' ? $default : $value;
    }

    public static function put(string $key, $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$cache = null;
    }
}
