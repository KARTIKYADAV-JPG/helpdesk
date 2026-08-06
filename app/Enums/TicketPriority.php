<?php

namespace App\Enums;

enum TicketPriority: string
{
    case LOW = 'Low';
    case MEDIUM = 'Medium';
    case HIGH = 'High';
    case URGENT = 'Urgent';

    /**
     * Get all values as a simple array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
