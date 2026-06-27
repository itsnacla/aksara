<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotRequestLog extends Model
{
    public $timestamps = false; // Only has created_at

    protected $fillable = [
        'invocation_id',
        'user_id',
        'message',
        'response',
        'status',
        'error_message',
        'provider',
        'model',
        'latency_seconds',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
