<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\WmActivityLog;
use Livewire\Component;

class ActivityLogList extends Component
{
    public $confirmingDeletion = false;

    public $log_id = null;

    public bool $isBulckDelete = false;

    protected $listeners = [
        'confirmDelete' => 'confirmDelete',
        'viewLogDetails' => 'viewLogDetails',
    ];

    public function mount()
    {
        if (! checkPermission('tenant.activity_log.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }
    }

    public function viewLogDetails($logId = '')
    {
        return redirect()->to(tenant_route('tenant.activity-log.details', [
            'logId' => $logId,
        ]));
    }

    public function updatedConfirmingDeletion($value)
    {
        if (! $value) {
            $this->js('window.pgBulkActions.clearAll()');
        }
    }

    public function confirmDelete($logId = '')
    {
        if (WmActivityLog::count() === 0) {
            $this->notify([
                'type' => 'danger',
                'message' => t('no_activity_log_found'),
            ]);

            return;
        }
        $this->log_id = $logId;
        $this->isBulckDelete = is_array($this->log_id) && count($this->log_id) !== 1 ? true : false;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('tenant.activity_log.delete')) {
            if (is_array($this->log_id) && count($this->log_id) !== 0) {
                $deletedCount = WmActivityLog::whereIn('id', $this->log_id)->delete();
                $this->log_id = null;
                $this->js('window.pgBulkActions.clearAll()');
                $this->notify([
                    'type' => 'danger',
                    'message' => $deletedCount.t('activity_logs_deleted'),
                ]);
            } elseif (! empty($this->log_id)) {
                $delete = WmActivityLog::find($this->log_id);
                if ($delete) {
                    $delete->delete();
                    $this->notify(['type' => 'danger', 'message' => t('log_deleted')]);
                } else {
                    $this->notify(['type' => 'danger', 'message' => t('log_not_found')]);
                }
            } else {
                $clearlog = WmActivityLog::where('tenant_id', tenant_id())->delete();
                $clearlog ? $this->notify(['type' => 'danger', 'message' => t('clear_all_logs')])
                    : $this->notify(['type' => 'danger', 'message' => t('log_not_found')]);
            }

            $this->confirmingDeletion = false;
            $this->dispatch('pg:eventRefresh-wm-activity-table');
        }
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-mwm-activity-table');
    }

    public function render()
    {
        return view('livewire.tenant.activity-log-list');
    }
}
