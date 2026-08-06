<?php

namespace App\Enums;

enum TicketStatus: string
{
    case NEW = 'new';
    case PROCESSING = 'processing';
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    /**
     * Get all values as a simple array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Explicit label mapping for status values in Title Case.
     */
    public static function labels(): array
    {
        return [
            self::NEW->value => 'New',
            self::PROCESSING->value => 'Processing',
            self::OPEN->value => 'Open',
            self::IN_PROGRESS->value => 'In Progress',
            self::RESOLVED->value => 'Resolved',
            self::CLOSED->value => 'Closed',
        ];
    }

    /**
     * Resolve a TicketStatus case from case-insensitive string input.
     */
    public static function fromInput(?string $value): ?self
    {
        if (empty($value)) {
            return null;
        }

        foreach (self::cases() as $case) {
            if (strcasecmp($case->value, $value) === 0 || strcasecmp($case->name, $value) === 0) {
                return $case;
            }
        }

        return self::tryFrom($value);
    }
}
