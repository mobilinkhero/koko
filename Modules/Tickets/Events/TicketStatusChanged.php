<?php

namespace Modules\Tickets\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Tickets\Models\Ticket;

class TicketStatusChanged
{
    use Dispatchable;

    public function __construct(
        public Ticket $ticket,
        public string $oldStatus,
        public bool $sendNotification = true,
    ) {}
}
