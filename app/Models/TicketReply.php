<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketReply extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'body',
        'sender_type',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'senderType',
    ];

    /**
     * Get the senderType attribute (accessor for camelCase).
     */
    public function getSenderTypeAttribute(): string
    {
        return $this->attributes['sender_type'] ?? 'customer';
    }

    /**
     * Set the senderType attribute (mutator for camelCase).
     */
    public function setSenderTypeAttribute($value): void
    {
        $this->attributes['sender_type'] = $value;
    }

    /**
     * Get the ticket that owns the reply.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user who submitted the reply.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
