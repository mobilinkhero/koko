<?php

declare(strict_types=1);

namespace Modules\Tickets\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Tickets\Models\Department;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'General Support',
                'Technical Support',
                'Billing',
                'Sales',
                'Product Information',
                'Website Issues',
                'API Support',
                'Account Management',
                'Feature Requests',
                'Bug Reports',
            ]),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function inactive(): static
    {
        return $this->state(function () {
            return [
                'is_active' => false,
            ];
        });
    }
}
