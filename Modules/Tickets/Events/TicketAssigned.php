<?php

namespace Modules\Tickets\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Tickets\Models\Ticket;

class TicketAssigned
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  Ticket  $ticket  The ticket that has been assigned
     */
    public function __construct(
        public Ticket $ticket,
    ) {}
}
