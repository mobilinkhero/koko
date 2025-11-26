<?php

namespace Modules\Tickets\Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Models\TicketReply;
use Tests\TestCase;

class TicketModelTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create();
        $this->department = Department::create([
            'name' => 'Technical Support',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_can_create_a_ticket(): void
    {
        $ticket = Ticket::create([
            'subject' => 'Test Subject',
            'description' => 'Test Description',
            'priority' => 'medium',
            'status' => 'open',
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
        ]);

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertEquals('Test Subject', $ticket->subject);
        $this->assertNotNull($ticket->ticket_number);
    }

    /** @test */
    public function it_belongs_to_a_client(): void
    {
        $ticket = Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
        ]);

        $this->assertInstanceOf(User::class, $ticket->client);
        $this->assertEquals($this->client->id, $ticket->client->id);
    }

    /** @test */
    public function it_belongs_to_a_department(): void
    {
        $ticket = Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
        ]);

        $this->assertInstanceOf(Department::class, $ticket->department);
        $this->assertEquals($this->department->id, $ticket->department->id);
    }

    /** @test */
    public function it_has_many_replies(): void
    {
        $ticket = Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
        ]);

        $reply = TicketReply::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->client->id,
        ]);

        $this->assertCount(1, $ticket->replies);
        $this->assertInstanceOf(TicketReply::class, $ticket->replies->first());
    }

    /** @test */
    public function it_can_check_if_overdue(): void
    {
        // Create a ticket that's 2 days old
        $ticket = Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
            'created_at' => now()->subDays(2),
            'priority' => 'high',
        ]);

        // For high priority, SLA is typically 24 hours
        $this->assertTrue($ticket->isOverdue());
    }

    /** @test */
    public function it_can_get_priority_badge_class(): void
    {
        $ticket = Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
            'priority' => 'urgent',
        ]);

        $this->assertEquals('danger', $ticket->getPriorityBadgeClass());
    }

    /** @test */
    public function it_can_get_status_badge_class(): void
    {
        $ticket = Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
            'status' => 'closed',
        ]);

        $this->assertEquals('secondary', $ticket->getStatusBadgeClass());
    }

    /** @test */
    public function it_can_scope_by_status(): void
    {
        Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
            'status' => 'open',
        ]);

        Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
            'status' => 'closed',
        ]);

        $openTickets = Ticket::byStatus('open')->get();
        $closedTickets = Ticket::byStatus('closed')->get();

        $this->assertCount(1, $openTickets);
        $this->assertCount(1, $closedTickets);
    }

    /** @test */
    public function it_can_scope_by_priority(): void
    {
        Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
            'priority' => 'high',
        ]);

        Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
            'priority' => 'low',
        ]);

        $highPriorityTickets = Ticket::byPriority('high')->get();
        $lowPriorityTickets = Ticket::byPriority('low')->get();

        $this->assertCount(1, $highPriorityTickets);
        $this->assertCount(1, $lowPriorityTickets);
    }

    /** @test */
    public function it_generates_unique_ticket_numbers(): void
    {
        $ticket1 = Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
        ]);

        $ticket2 = Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
        ]);

        $this->assertNotEquals($ticket1->ticket_number, $ticket2->ticket_number);
        $this->assertIsString($ticket1->ticket_number);
        $this->assertIsString($ticket2->ticket_number);
    }
}
