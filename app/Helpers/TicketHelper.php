<?php

namespace App\Helpers;

use App\Models\User;
use Modules\Tickets\Models\Department;

class TicketHelper
{
    /**
     * Get the assigned users for a department
     *
     * @param  int|null  $departmentId
     * @return array Returns array of assigned user info
     */
    public static function getAssignedUserForDepartment($departmentId): array
    {
        // Handle empty string, null, or 0
        if (empty($departmentId)) {
            return [];
        }

        // Convert to integer
        $departmentId = (int) $departmentId;

        $department = Department::find($departmentId);

        if (! $department || empty($department->assignee_id)) {
            return [];
        }

        // Support multiple assignees
        $assigneeIds = is_array($department->assignee_id) ?
            $department->assignee_id :
            [$department->assignee_id];

        return User::whereIn('id', $assigneeIds)
            ->where('user_type', 'admin')
            ->get()
            ->map(function ($user) use ($department) {
                return [
                    'user_id' => $user->id,
                    'department_name' => $department->name,
                    'name' => $user->firstname.' '.$user->lastname,
                    'email' => $user->email,
                ];
            })
            ->toArray();
    }

    /**
     * Get all assigned users for a department
     *
     * @return array Returns array of all assignee users
     */
    public static function getAllAssignedUsersForDepartment(?int $departmentId): array
    {
        if (empty($departmentId)) {
            return [];
        }

        $department = Department::find($departmentId);

        if (! $department || empty($department->assignee_id)) {
            return [];
        }

        $assigneeIds = is_array($department->assignee_id) ?
            $department->assignee_id :
            [$department->assignee_id];

        return User::whereIn('id', $assigneeIds)
            ->where('user_type', 'admin')
            ->select('id', 'firstname', 'lastname', 'email')
            ->get()
            ->map(fn ($user) => [
                'user_id' => $user->id,
                'name' => $user->firstname.' '.$user->lastname,
                'email' => $user->email,
            ])
            ->toArray();
    }

    /**
     * Auto assign a ticket to department's default users
     *
     * @return array Returns assigned user IDs
     */
    public static function autoAssignTicket(?int $departmentId): array
    {
        if ($department = Department::find($departmentId)) {
            return is_array($department->assignee_id) ?
                $department->assignee_id :
                [$department->assignee_id];
        }

        return [];
    }

    /**
     * Get all admin users that should receive ticket notifications
     *
     * @return array Returns array of admin user emails
     */
    public static function getAdminUsersForNotification(): array
    {
        return User::withoutGlobalScopes()->where('user_type', 'admin')
            ->where('is_admin', true)
            ->where('active', true)
            ->pluck('email')
            ->toArray();
    }
}
