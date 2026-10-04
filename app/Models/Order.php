<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
        'total' => 'float',
        'ready_date' => 'date',
    ];

    public const STATUSES = [
        'new' => 'Новый',
        'confirmed' => 'Подтверждён',
        'done' => 'Выполнен',
        'cancelled' => 'Отменён',
    ];
}
