<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TicketMessageAttachment extends Model
{
    protected $fillable = [
        'ticket_message_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
    ];
    protected $appends = ['file_url'];

    public function message()
    {
        return $this->belongsTo(
            TicketMessage::class,
            'ticket_message_id'
        );
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
