<?php

namespace Modules\Tickets\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Tickets\Models\Ticket;

class TicketCreated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  Ticket  $ticket  The newly created ticket
     * @param  string  $tenantId  The ID of the tenant who created the ticket
     */
    public function __construct(
        public Ticket $ticket,
        public string $tenantId
    ) {}
}
