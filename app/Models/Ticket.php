<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Enums\TicketCategory;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;

#[Fillable(['ticket_number', 'subject', 'description', 'summary', 'category', 'status', 'priority', 'created_by', 'assigned_to', 'resolved_at', 'email_message_id', 'raw_email'])]
class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'subject',
        'description',
        'summary',
        'category',
        'status',
        'priority',
        'created_by',
        'assigned_to',
        'resolved_at',
        'email_message_id',
        'raw_email',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'category' => TicketCategory::class,
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created the ticket.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the agent assigned to the ticket.
     */
    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the replies for the ticket.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at', 'asc');
    }

    /**
     * Scope a query to sort by allowed columns and directions.
     */
    public function scopeSorted($query, string $field = 'created_at', string $direction = 'desc')
    {
        $allowedFields = ['created_at', 'subject', 'status', 'priority'];
        $allowedDirections = ['asc', 'desc'];

        $field = in_array($field, $allowedFields) ? $field : 'created_at';
        $direction = in_array(strtolower($direction), $allowedDirections) ? strtolower($direction) : 'desc';

        return $query->orderBy($field, $direction);
    }

    /**
     * Scope a query to filter by various attributes.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['status'] ?? null, function ($q, $status) {
            $q->where('status', $status);
        });

        $query->when($filters['category'] ?? null, function ($q, $category) {
            $q->where('category', $category);
        });

        $query->when($filters['priority'] ?? null, function ($q, $priority) {
            $q->where('priority', $priority);
        });

        $query->when($filters['assigned_to'] ?? null, function ($q, $assignedTo) {
            if ($assignedTo === 'unassigned') {
                $q->whereNull('assigned_to');
            } else {
                $q->where('assigned_to', $assignedTo);
            }
        });

        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('ticket_number', 'ilike', '%' . $search . '%')
                         ->orWhere('subject', 'ilike', '%' . $search . '%');
            });
        });

        return $query;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $counter = (int) self::max('id') + 1;
                do {
                    $number = 'TKT-' . str_pad($counter, 5, '0', STR_PAD_LEFT);
                    $counter++;
                } while (self::where('ticket_number', $number)->exists());
                $ticket->ticket_number = $number;
            }
        });
    }
}
