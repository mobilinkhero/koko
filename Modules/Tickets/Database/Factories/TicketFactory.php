<?php

declare(strict_types=1);

namespace Modules\Tickets\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\Ticket;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        $statuses = ['open', 'pending', 'answered', 'closed', 'on_hold'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        return [
            'ticket_number' => 'TKT'.str_pad((string) fake()->unique()->randomNumber(6), 6, '0', STR_PAD_LEFT),
            'subject' => fake()->sentence(),
            'department_id' => function () {
                return Department::inRandomOrder()->first()?->id ?? Department::factory()->create()->id;
            },
            'client_id' => function () {
                return User::where('role_id', '!=', 1)->inRandomOrder()->first()?->id ?? User::factory()->create(['role_id' => 2])->id;
            },
            'assigned_to' => null,
            'status' => fake()->randomElement($statuses),
            'priority' => fake()->randomElement($priorities),
            'admin_viewed' => fake()->boolean(70),
            'client_viewed' => fake()->boolean(60),
            'last_reply_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Ticket $ticket) {
            // Create the initial message
            $ticket->messages()->create([
                'message' => fake()->paragraph(3),
                'user_id' => $ticket->client_id,
                'user_type' => 'client',
            ]);

            // Add random replies (0-5 additional messages)
            $replyCount = rand(0, 5);
            for ($i = 0; $i < $replyCount; $i++) {
                $isAdmin = fake()->boolean();
                $ticket->messages()->create([
                    'message' => fake()->paragraphs(rand(1, 3), true),
                    'user_id' => $isAdmin ? User::where('role_id', 1)->inRandomOrder()->first()?->id : $ticket->client_id,
                    'user_type' => $isAdmin ? 'admin' : 'client',
                    'created_at' => fake()->dateTimeBetween($ticket->created_at, 'now'),
                ]);
            }

            // Update last_reply_at to the latest message date
            $latestMessage = $ticket->messages()->latest()->first();
            if ($latestMessage) {
                $ticket->update(['last_reply_at' => $latestMessage->created_at]);
            }
        });
    }

    /**
     * Generate an open ticket.
     */
    public function open(): static
    {
        return $this->state(function () {
            return ['status' => 'open'];
        });
    }

    /**
     * Generate a pending ticket.
     */
    public function pending(): static
    {
        return $this->state(function () {
            return ['status' => 'pending'];
        });
    }

    /**
     * Generate an answered ticket.
     */
    public function answered(): static
    {
        return $this->state(function () {
            return ['status' => 'answered'];
        });
    }

    /**
     * Generate a closed ticket.
     */
    public function closed(): static
    {
        return $this->state(function () {
            return ['status' => 'closed'];
        });
    }

    /**
     * Generate a high priority ticket.
     */
    public function highPriority(): static
    {
        return $this->state(function () {
            return ['priority' => fake()->randomElement(['high', 'urgent'])];
        });
    }

    /**
     * Generate a ticket with attachments.
     */
    public function withAttachments(int $count = 1): static
    {
        return $this->afterCreating(function (Ticket $ticket) use ($count) {
            for ($i = 0; $i < $count; $i++) {
                $ticket->attachments()->create([
                    'filename' => fake()->word().'.'.fake()->randomElement(['pdf', 'jpg', 'png', 'docx']),
                    'path' => 'ticket-attachments/'.fake()->uuid().'.pdf',
                    'size' => fake()->numberBetween(1000, 5000000),
                    'mime_type' => fake()->randomElement(['application/pdf', 'image/jpeg', 'image/png', 'application/msword']),
                    'user_id' => $ticket->client_id,
                ]);
            }
        });
    }
}
