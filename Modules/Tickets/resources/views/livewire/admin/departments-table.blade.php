<div>
    @push('styles')
    <style>
        .departments-table-container {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #374151;
            background-color: #f9fafb;
        }

        .badge {
            font-size: 0.75rem;
            font-weight: 500;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.775rem;
        }

        .department-name {
            font-weight: 600;
            color: #374151;
        }

        .department-description {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .translation-info {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .language-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }

        .tickets-count {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .count-badge {
            background-color: #e5e7eb;
            color: #374151;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .action-buttons .btn {
            margin-right: 0.25rem;
        }

        .action-buttons .btn:last-child {
            margin-right: 0;
        }

        .powergrid-table {
            margin-bottom: 0;
        }

        .powergrid-footer {
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 1rem;
        }

        .table-responsive {
            border-radius: 0.5rem;
        }
    </style>
    @endpush

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="btn-group" role="group">
                <button type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#createDepartmentModal">
                    <i class="bi bi-plus-circle"></i> {{ t('new_department') }}
                </button>
                <button type="button" class="btn btn-outline-secondary" id="bulk-actions-btn" disabled>
                    <i class="bi bi-gear"></i> {{ t('bulk_actions') }}
                </button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="d-flex justify-content-end">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshDepartments()">
                        <i class="bi bi-arrow-clockwise"></i> {{ t('refresh') }}
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-funnel"></i> {{ t('quick_filters') }}
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('status', 'active')">{{ t('active_departments') }}</a></li>
                            <li><a class="dropdown-item" href="#" onclick="applyFilter('status', 'inactive')">{{ t('inactive_departments') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="clearFilters()">{{ t('clear_all_filters') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="departments-table-container">
        {{ $slot }}
    </div>

    <!-- Create/Edit Department Modal -->
    <div class="modal fade" id="createDepartmentModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $editingDepartment ? {{ t('edit_department') }} : {{ t('create_department') }} }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit="saveDepartment">
                    <div class="modal-body">
                        <!-- Department Name -->
                        <div class="mb-3">
                            <label for="departmentName" class="form-label">{{ t('department_name') }}<span class="text-danger">*</span></label>
                            <input type="text"
                                   wire:model="form.name"
                                   class="form-control @error('form.name') is-invalid @enderror"
                                   id="departmentName"
                                   placeholder="Enter department name">
                            @error('form.name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Department Description -->
                        <div class="mb-3">
                            <label for="departmentDescription" class="form-label">{{ t('description') }}</label>
                            <x-textarea
                                wire:model="form.description"
                                id="departmentDescription"
                                rows="3"
                                placeholder="Enter department description (optional)"
                                class="form-control @error('form.description') is-invalid @enderror"
                            />
                            @error('form.description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="departmentStatus" class="form-label">{{ t('status') }}</label>
                            <select wire:model="form.status"
                                    class="form-select @error('form.status') is-invalid @enderror"
                                    id="departmentStatus">
                                <option value="active">{{ t('active') }}</option>
                                <option value="inactive">{{ t('inactive') }}</option>
                            </select>
                            @error('form.status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Translations -->
                        <div class="mb-3">
                            <label class="form-label">{{ t('translations') }}</label>
                            <div class="translations-container">
                                @foreach($availableLocales as $locale => $language)
                                    <div class="translation-group mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <img src="/flags/{{ $locale }}.png" alt="{{ $language }}" width="16" class="me-1">
                                                {{ $language }}
                                            </span>
                                            <input type="text"
                                                   wire:model="form.translations.{{ $locale }}"
                                                   class="form-control @error('form.translations.' . $locale) is-invalid @enderror"
                                                   placeholder="Department name in {{ $language }}">
                                        </div>
                                        @error('form.translations.' . $locale)
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                            <small class="form-text text-muted">
                                {{ t('Provide_translations_different_languages') }}
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ t('cancel') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                {{ $editingDepartment ? 'Update Department' : 'Create Department' }}
                            </span>
                            <span wire:loading>
                                <i class="bi bi-hourglass-split"></i> {{ t('saving') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Modal -->
    <div class="modal fade" id="bulkActionsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ t('bulk_actions') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ t('action') }}</label>
                        <select class="form-select" id="bulk-action-type">
                            <option value="">{{ t('select_action') }}</option>
                            <option value="activate">{{ t('activate_departments') }}</option>
                            <option value="deactivate">{{ t('deactivate_departments') }}</option>
                            <option value="delete">{{ t('delete_departments') }}</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <span id="selected-departments-count">0</span> {{ t('departments_selected') }}
                    </div>
                    <div class="alert alert-warning" id="delete-warning" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        {{ t('deleting_departments_associated_tickets') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ t('cancel') }}</button>
                    <button type="button" class="btn btn-primary" onclick="executeBulkAction()">{{ t('apply') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function refreshDepartments() {
                @this.call('$refresh');
            }

            function applyFilter(field, value) {
                @this.call('applyFilter', field, value);
            }

            function clearFilters() {
                @this.call('clearFilters');
            }

            function editDepartment(departmentId) {
                @this.call('editDepartment', departmentId).then(() => {
                    new bootstrap.Modal(document.getElementById('createDepartmentModal')).show();
                });
            }

            function deleteDepartment(departmentId) {
                if (confirm({{ t('delete_department_message') }})) {
                    @this.call('deleteDepartment', departmentId);
                }
            }

            function toggleDepartmentStatus(departmentId) {
                @this.call('toggleStatus', departmentId);
            }

            // Bulk actions functionality
            document.addEventListener('DOMContentLoaded', function() {
                const bulkActionType = document.getElementById('bulk-action-type');
                const deleteWarning = document.getElementById('delete-warning');

                if (bulkActionType) {
                    bulkActionType.addEventListener('change', function() {
                        if (this.value === 'delete') {
                            deleteWarning.style.display = 'block';
                        } else {
                            deleteWarning.style.display = 'none';
                        }
                    });
                }

                // Handle row selection for bulk actions
                document.addEventListener('change', function(e) {
                    if (e.target.type === 'checkbox' && (e.target.name === 'pg-checkbox[]' || e.target.id === 'pg-checkbox-all')) {
                        updateBulkActionsButton();
                    }
                });
            });

            function updateBulkActionsButton() {
                const checkboxes = document.querySelectorAll('input[name="pg-checkbox[]"]:checked');
                const bulkBtn = document.getElementById('bulk-actions-btn');
                const selectedCount = document.getElementById('selected-departments-count');

                if (checkboxes.length > 0) {
                    bulkBtn.disabled = false;
                    if (selectedCount) {
                        selectedCount.textContent = checkboxes.length;
                    }
                } else {
                    bulkBtn.disabled = true;
                    if (selectedCount) {
                        selectedCount.textContent = '0';
                    }
                }
            }

            function executeBulkAction() {
                const actionType = document.getElementById('bulk-action-type').value;

                if (!actionType) {
                    alert({{ t('please_select_an_action') }});
                    return;
                }

                const selectedDepartments = getSelectedDepartments();

                if (selectedDepartments.length === 0) {
                    alert({{ t('select_departments_bulk_action') }});
                    return;
                }

                let confirmMessage = '';
                switch(actionType) {
                    case 'activate':
                        confirmMessage = `Activate ${selectedDepartments.length} department(s)?`;
                        break;
                    case 'deactivate':
                        confirmMessage = `Deactivate ${selectedDepartments.length} department(s)?`;
                        break;
                    case 'delete':
                        confirmMessage = `Delete ${selectedDepartments.length} department(s)? This action cannot be undone.`;
                        break;
                }

                if (confirm(confirmMessage)) {
                    @this.call('bulkAction', {
                        action: actionType,
                        departments: selectedDepartments
                    });

                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('bulkActionsModal')).hide();
                }
            }

            function getSelectedDepartments() {
                const checkboxes = document.querySelectorAll('input[name="pg-checkbox[]"]:checked');
                return Array.from(checkboxes).map(checkbox => checkbox.value);
            }

            // Livewire events
            window.addEventListener('department-saved', event => {
                showToast('success',  t('department_saved_successfully') );
                bootstrap.Modal.getInstance(document.getElementById('createDepartmentModal')).hide();
            });

            window.addEventListener('department-deleted', event => {
                showToast('success', t('department_deleted_successfully'));
            });

            window.addEventListener('departments-updated', event => {
                showToast('success', t('departments_updated_successfully'));
                updateBulkActionsButton();
            });

            window.addEventListener('bulk-action-completed', event => {
                showToast('success', event.detail.message);
                updateBulkActionsButton();
            });

            // Reset form when modal is hidden
            document.getElementById('createDepartmentModal').addEventListener('hidden.bs.modal', function() {
                @this.call('resetForm');
            });

            function showToast(type, message) {
                const toast = document.createElement('div');
                toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                toast.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 5000);
            }
        </script>
    @endpush
</div>
