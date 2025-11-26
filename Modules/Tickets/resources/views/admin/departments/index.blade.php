<x-app-layout>

@section('title', 'Ticket Departments')

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ t('dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.tickets.index') }}">{{ t('tickets') }}</a></li>
                <li class="breadcrumb-item active">Departments</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">{{ t('ticket_departments') }}</h1>
                <p class="text-muted mb-0">{{ t('manage_departments_for_ticket') }}</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-ticket-perforated"></i> {{ t('view_tickets') }}
                </a>
                <button type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#createDepartmentModal">
                    <i class="bi bi-plus-circle"></i> {{ t('new_department') }}
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                    <i class="bi bi-building text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-semibold text-muted mb-1">{{ t('total_departments') }}</div>
                                <div class="h4 mb-0">{{ $stats['total_departments'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                    <i class="bi bi-check-circle text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-semibold text-muted mb-1">{{ t('active_departments') }}</div>
                                <div class="h4 mb-0">{{ $stats['active_departments'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                    <i class="bi bi-pause-circle text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-semibold text-muted mb-1">{{ t('inactive_departments') }}</div>
                                <div class="h4 mb-0">{{ $stats['inactive_departments'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                    <i class="bi bi-globe text-info fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-semibold text-muted mb-1">{{ t('languages_supported') }}</div>
                                <div class="h4 mb-0">{{ $stats['languages_count'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departments Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">
                    <i class="bi bi-table"></i>
                    {{ t('departments_management') }}
                </h5>
            </div>
            <div class="card-body p-0">
                @livewire('Tickets::admin.departments-table')
            </div>
        </div>

        <!-- Usage Guidelines -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-info-circle"></i>
                            {{ t('department_management_guidelines') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">{{ t('best_practices') }}</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <strong>{{ t('clear_names') }}</strong> {{ t('use_descriptive_understandable_department') }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <strong>{{ t('translations') }}</strong> {{ t('provide_translations_supported_languages') }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <strong>{{ t('logical_structure') }}</strong> {{ t('organize_departments_by_function') }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-warning">{{ t('important_notes') }}</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                        <strong>{{ t('active_status') }}</strong> {{ t('only_active_departments_appear') }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                        <strong>{{ t('deletion_impact') }}</strong> {{ t('deleting_departments_affects_existing_tickets') }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                        <strong>{{ t('default_languages') }}</strong> {{ t('always_provide_the_default_language') }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-lightbulb"></i>
                            {{ t('quick_actions') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button"
                                    class="btn btn-outline-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#createDepartmentModal">
                                <i class="bi bi-plus-circle"></i> {{ t('create_department') }}
                            </button>
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    onclick="exportDepartments()">
                                <i class="bi bi-download"></i> {{ t('export_departments') }}
                            </button>
                            <button type="button"
                                    class="btn btn-outline-info btn-sm"
                                    onclick="showTranslationStats()">
                                <i class="bi bi-translate"></i> {{ t('translation_stats') }}
                            </button>
                        </div>

                        <hr class="my-3">

                        <div class="text-center">
                            <small class="text-muted">
                                {{ t('need_help') }} <a href="#" onclick="showHelp()">{{ t('view_documentation') }}</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Translation Statistics Modal -->
    <div class="modal fade" id="translationStatsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ t('translation_statistics') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ t('language') }}</th>
                                    <th>{{ t('departments_translated') }}</th>
                                    <th>{{ t('completion_rate') }}</th>
                                    <th>{{ t('missing_translations') }}</th>
                                </tr>
                            </thead>
                            <tbody id="translation-stats-body">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ t('close') }}</button>
                </div>
            </div>
        </div>
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

    .card {
        transition: transform 0.15s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .bg-opacity-10 {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    }

    .fs-4 {
        font-size: 1.5rem !important;
    }

    .fw-semibold {
        font-weight: 600 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function exportDepartments() {
        // Implement export functionality
        window.location.href = '{{ route("admin.tickets.departments.export") }}';
    }

    function showTranslationStats() {
        // Fetch translation statistics and show modal
        fetch('{{ route("admin.tickets.departments.translation-stats") }}')
            .then(response => response.json())
            .then(data => {
                populateTranslationStats(data);
                new bootstrap.Modal(document.getElementById('translationStatsModal')).show();
            })
            .catch(error => {
                console.error('Error fetching translation stats:', error);
                alert('Error loading translation statistics');
            });
    }

    function populateTranslationStats(stats) {
        const tbody = document.getElementById('translation-stats-body');
        tbody.innerHTML = '';

        stats.forEach(stat => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <img src="/flags/${stat.locale}.png" alt="${stat.language}" width="16" class="me-2">
                    ${stat.language}
                </td>
                <td>${stat.translated_count}</td>
                <td>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar ${stat.completion_rate >= 80 ? 'bg-success' : stat.completion_rate >= 50 ? 'bg-warning' : 'bg-danger'}"
                             style="width: ${stat.completion_rate}%">
                            ${stat.completion_rate}%
                        </div>
                    </div>
                </td>
                <td>
                    ${stat.missing_count > 0 ?
                        `<span class="badge bg-warning">${stat.missing_count}</span>` :
                        '<span class="text-success">Complete</span>'
                    }
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function showHelp() {
        // Open help documentation
        window.open('/docs/tickets/departments', '_blank');
    }
</script>
@endpush

</x-app-layout>
