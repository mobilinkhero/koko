<?php

namespace Modules\Tickets\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Models\TicketReply;

class TicketReplyFactory extends Factory
{
    protected $model = TicketReply::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'user_type' => $this->faker->randomElement(['admin', 'client', 'system']),
            'attachments' => $this->faker->optional(0.15)->randomElements([
                ['filename' => 'reply_document.pdf', 'size' => 512000, 'url' => '/storage/ticket-replies/reply_document.pdf'],
                ['filename' => 'reply_image.png', 'size' => 128000, 'url' => '/storage/ticket-replies/reply_image.png'],
            ], $this->faker->numberBetween(1, 2)),
            'viewed' => $this->faker->boolean(60),
            'content' => $this->faker->paragraphs($this->faker->numberBetween(1, 3), true),
        ];
    }

    public function fromAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'admin',
            'viewed' => $this->faker->boolean(80),
        ]);
    }

    public function fromClient(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'client',
            'viewed' => $this->faker->boolean(40),
        ]);
    }

    public function fromSystem(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'system',
            'viewed' => true,
            'content' => $this->faker->randomElement([
                'Ticket has been automatically assigned to the support team.',
                'Priority has been updated based on keywords detected.',
                'Ticket has been escalated due to response time.',
                'Department has been changed based on ticket content.',
            ]),
        ]);
    }

    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachments' => [
                ['filename' => 'attachment.pdf', 'size' => 256000, 'url' => '/storage/ticket-replies/attachment.pdf'],
            ],
        ]);
    }

    public function viewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'viewed' => true,
        ]);
    }

    public function unviewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'viewed' => false,
        ]);
    }
}
