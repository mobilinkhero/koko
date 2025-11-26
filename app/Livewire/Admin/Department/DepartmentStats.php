<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Department;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\Ticket;

class DepartmentStats extends Component
{
    #[Computed]
    public function departmentStats(): array
    {
        return [
            'total_departments' => Department::count(),
            'active_departments' => Department::where('status', true)->count(),
            'inactive_departments' => Department::where('status', false)->count(),
            'departments_with_tickets' => Department::has('tickets')->count(),
            'departments_without_tickets' => Department::doesntHave('tickets')->count(),
            'total_tickets' => Ticket::count(),
            'tickets_by_department' => Department::withCount('tickets')
                ->orderBy('tickets_count', 'desc')
                ->limit(3)
                ->get(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.department.department-stats', [
            'stats' => $this->departmentStats,
        ]);
    }
}
