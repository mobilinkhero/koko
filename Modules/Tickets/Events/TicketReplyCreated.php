<?php

namespace Modules\Tickets\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Tickets\Models\TicketReply;

class TicketReplyCreated
{
    use Dispatchable;
    use SerializesModels;

    public TicketReply $reply;

    public bool $sendNotification;

    /**
     * Create a new event instance.
     */
    public function __construct(TicketReply $reply, bool $sendNotification = true)
    {
        $this->reply = $reply;
        $this->sendNotification = $sendNotification;
    }
}
