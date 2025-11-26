<?php

namespace Modules\Tickets\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\Tickets\Models\Department;
use Modules\Tickets\Models\Ticket;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $admin;

    private User $client;

    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User',
        ]);

        $this->client = User::factory()->create([
            'email' => 'client@example.com',
            'name' => 'Client User',
        ]);

        // Create test department
        $this->department = Department::create([
            'name' => 'Technical Support',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_view_tickets_index(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.tickets.index'));

        $response->assertStatus(200);
        $response->assertViewIs('Tickets::admin.tickets.index');
    }

    /** @test */
    public function admin_can_create_ticket(): void
    {
        $this->actingAs($this->admin);

        $ticketData = [
            'subject' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'priority' => 'medium',
            'department_id' => $this->department->id,
            'client_id' => $this->client->id,
        ];

        $response = $this->post(route('admin.tickets.store'), $ticketData);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'subject' => $ticketData['subject'],
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
        ]);
    }

    /** @test */
    public function client_can_view_their_tickets(): void
    {
        $this->actingAs($this->client);

        // Create a ticket for this client
        $ticket = Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
        ]);

        $response = $this->get(tenant_route('tenant.tickets.index'));

        $response->assertStatus(200);
        $response->assertViewIs('Tickets::client.tickets.index');
    }

    /** @test */
    public function client_can_create_ticket(): void
    {
        $this->actingAs($this->client);

        $ticketData = [
            'subject' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'priority' => 'medium',
            'department_id' => $this->department->id,
        ];

        $response = $this->post(tenant_route('tenant.tickets.store'), $ticketData);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'subject' => $ticketData['subject'],
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
        ]);
    }

    /** @test */
    public function client_cannot_view_other_client_tickets(): void
    {
        $this->actingAs($this->client);

        $otherClient = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'client_id' => $otherClient->id,
            'department_id' => $this->department->id,
        ]);

        $response = $this->get(tenant_route('tenant.tickets.show', ['ticket' => $ticket->id]));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_add_reply_to_ticket(): void
    {
        $this->actingAs($this->admin);

        $ticket = Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
        ]);

        $replyData = [
            'message' => $this->faker->paragraph,
            'is_internal' => false,
        ];

        $response = $this->post(route('admin.tickets.reply', $ticket->id), $replyData);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'message' => $replyData['message'],
            'user_id' => $this->admin->id,
            'user_type' => 'admin',
        ]);
    }

    /** @test */
    public function client_can_add_reply_to_their_ticket(): void
    {
        $this->actingAs($this->client);

        $ticket = Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
        ]);

        $replyData = [
            'message' => $this->faker->paragraph,
        ];

        $response = $this->post(tenant_route('tenant.tickets.reply', $ticket->id), $replyData);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'message' => $replyData['message'],
            'user_id' => $this->client->id,
            'user_type' => 'client',
        ]);
    }

    /** @test */
    public function admin_can_update_ticket_status(): void
    {
        $this->actingAs($this->admin);

        $ticket = Ticket::factory()->create([
            'client_id' => $this->client->id,
            'department_id' => $this->department->id,
            'status' => 'open',
        ]);

        $response = $this->post(route('admin.tickets.update-status', $ticket->id), [
            'status' => 'closed',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'closed',
        ]);
    }

    /** @test */
    public function ticket_generates_unique_ticket_number(): void
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
        $this->assertNotNull($ticket1->ticket_number);
        $this->assertNotNull($ticket2->ticket_number);
    }
}
