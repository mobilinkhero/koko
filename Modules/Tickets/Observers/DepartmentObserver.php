<?php

namespace Modules\Tickets\Observers;

use Illuminate\Support\Facades\Cache;
use Modules\Tickets\Models\Department;

class DepartmentObserver
{
    /**
     * Handle the Department "created" event.
     */
    public function created(Department $department): void
    {
        // Clear the department cache when a department is created
        Cache::forget('ticket_filter_departments');
    }

    /**
     * Handle the Department "updated" event.
     */
    public function updated(Department $department): void
    {
        // Clear the department cache when a department is updated
        Cache::forget('ticket_filter_departments');
    }

    /**
     * Handle the Department "deleted" event.
     */
    public function deleted(Department $department): void
    {
        // Clear the department cache when a department is deleted
        Cache::forget('ticket_filter_departments');
    }
}
