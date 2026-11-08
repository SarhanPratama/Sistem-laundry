<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'contact',
        'business_name',
        'message',
        'is_read',
        'reply_subject',
        'reply_message',
        'replied_at',
        'replied_by',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    public function isReplied(): bool
    {
        return ! empty($this->replied_at);
    }

    public function repliedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by')->withTrashed();
    }
}
