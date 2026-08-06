<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin()
            || $user->isAgent()
            || $ticket->created_by === $user->id
            || $ticket->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $user->isAgent();
    }
}
