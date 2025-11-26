<?php

namespace Modules\Tickets\Observers;

use Illuminate\Support\Facades\Cache;
use Modules\Tickets\Models\Ticket;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        // Clear the related caches when a ticket is created
        $this->clearCaches();
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        // Clear the related caches when a ticket is updated
        $this->clearCaches();
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        // Clear the related caches when a ticket is deleted
        $this->clearCaches();
    }

    /**
     * Clear all ticket-related caches
     */
    protected function clearCaches(): void
    {
        Cache::forget('ticket_filter_tenants');
        Cache::forget('Tickets_count');
    }
}
