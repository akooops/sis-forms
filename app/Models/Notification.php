<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // Notification types
    const TYPE_FORM_SUBMISSION = 'form_submission';

    // Icons for different notification types
    const ICONS = [
        self::TYPE_FORM_SUBMISSION => 'ki-filled ki-message-text-2',
    ];

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function markAsRead()
    {
        $this->update([
            'read_at' => now(),
        ]);
    }

    public function getIconAttribute($value)
    {
        return $value ?? self::ICONS[$this->type] ?? 'ki-filled ki-notification-status';
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
} 