<x-app-layout>

@section('title', 'Edit Ticket #' . $ticket->ticket_id)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.tickets.index') }}">Tickets</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.tickets.show', $ticket) }}">{{ $ticket->ticket_id }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Edit Ticket #{{ $ticket->ticket_id }}</h1>
            <p class="text-muted mb-0">Update ticket information and settings</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-outline-primary">
                <i class="bi bi-eye"></i> View Ticket
            </a>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    @livewire('tickets::admin.ticket-form', ['ticket' => $ticket])
</div>
@endsection

@push('styles')
<style>
    .container-fluid {
        max-width: 1400px;
    }

    .breadcrumb {
        background-color: transparent;
        padding: 0;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: var(--bs-breadcrumb-divider, "/");
    }

    .breadcrumb-item a {
        text-decoration: none;
        color: #6b7280;
    }

    .breadcrumb-item a:hover {
        color: #374151;
    }

    .breadcrumb-item.active {
        color: #374151;
    }
</style>
@endpush

</x-app-layout>
