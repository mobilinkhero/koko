<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Tickets\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Sales',
                'description' => 'Handles client relationships, lead generation, and deal closures.',
            ],
            [
                'name' => 'Technical',
                'description' => 'Responsible for development, deployment, and technical support.',
            ],
            [
                'name' => 'Quality Assurance',
                'description' => 'Ensures product meets quality standards through rigorous testing.',
            ],
            [
                'name' => 'General',
                'description' => 'Manages company-wide services like HR, admin, and operations.',
            ],
        ];

        foreach ($departments as $data) {
            Department::updateOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['description'],
                    'status' => true,
                    'assignee_id' => null,
                ]
            );
        }
    }
}
