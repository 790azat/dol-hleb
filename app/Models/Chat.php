<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $guarded = [];

    protected $casts = ['last_message_at' => 'datetime', 'unread' => 'boolean'];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }

    public function label(): string
    {
        return $this->name ? "{$this->name} (чат №{$this->id})" : "Посетитель (чат №{$this->id})";
    }
}
