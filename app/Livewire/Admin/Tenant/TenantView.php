<?php

namespace App\Livewire\Admin\Tenant;

use App\Models\Currency;
use App\Models\Invoice\Invoice;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class TenantView extends Component
{
    use WithPagination;

    public Tenant $tenant;

    public $confirmingStatusChange = false;

    public $newStatus = '';

    public $confirmingImpersonation = false;

    protected $listeners = [
        'refreshComponent' => '$refresh',
    ];

    public function mount($tenantId)
    {
        if (! checkPermission('admin.tenants.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
        $this->tenant = Tenant::with([
            'subscriptions.plan',
            'subscriptions.invoices' => function ($query) {
                $query->latest()->limit(10);
            },
            'adminUser',
        ])->findOrFail($tenantId);
    }

    public function confirmStatusChange($status)
    {
        $this->newStatus = $status;
        $this->confirmingStatusChange = true;
    }

    public function updateStatus()
    {

        if (checkPermission('admin.tenants.edit')) {
            if ($this->tenant->status !== $this->newStatus) {
                $oldStatus = $this->tenant->status;
                $this->tenant->update(['status' => $this->newStatus]);

                $this->notify([
                    'type' => 'success',
                    'message' => t('tenant_status_updated_successfully', ['status' => ucfirst($this->newStatus)]),
                ]);
            }

            $this->confirmingStatusChange = false;
            $this->dispatch('refreshComponent');
        }
    }

    public function confirmImpersonation($userId)
    {
        if (checkPermission('admin.tenants.login')) {
            return redirect()->to(route('admin.login.as', ['id' => $userId]));
        }
    }

    public function downloadInvoice($invoiceId)
    {
        if (checkPermission('admin.invoices.view')) {
            $invoice = Invoice::where('id', $invoiceId)
                ->where('tenant_id', $this->tenant->id)
                ->firstOrFail();

            return response()->download($invoice->savePdf(), $invoice->invoice_number.'.pdf');
        }
    }

    public function getActiveSubscriptionProperty()
    {
        return $this->tenant->subscriptions()
            ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIAL, Subscription::STATUS_NEW])
            ->with('plan')
            ->latest()
            ->first() ?? new Subscription;
    }

    public function getLatestInvoiceProperty()
    {
        return $this->tenant->subscriptions()
            ->with('invoices')
            ->get()
            ->flatMap->invoices
            ->sortByDesc('created_at')
            ->first();
    }

    public function getSubscriptionSummaryProperty()
    {
        return [
            'total_subscriptions' => $this->tenant->subscriptions()->count(),
            'active_subscriptions' => $this->tenant->subscriptions()->where('status', Subscription::STATUS_ACTIVE)->count(),
            'total_invoices' => Invoice::whereHas('subscription', function ($query) {
                $query->where('tenant_id', $this->tenant->id);
            })->count(),
            'paid_invoices' => Invoice::whereHas('subscription', function ($query) {
                $query->where('tenant_id', $this->tenant->id);
            })->where('status', Invoice::STATUS_PAID)->count(),
            'total_transactions' => Transaction::whereHas('invoice.subscription', function ($query) {
                $query->where('tenant_id', $this->tenant->id);
            })->count(),
            'successful_transactions' => Transaction::whereHas('invoice.subscription', function ($query) {
                $query->where('tenant_id', $this->tenant->id);
            })->where('status', Transaction::STATUS_SUCCESS)->count(),
            // Ticket statistics
            'total_tickets' => \Modules\Tickets\Models\Ticket::where('tenant_id', $this->tenant->id)->count(),

        ];
    }

    public function getTenantUsersProperty()
    {
        return User::where('tenant_id', $this->tenant->id)
            ->paginate(10, ['*'], 'usersPage');
    }

    public function render()
    {
        return view('livewire.admin.tenant.tenant-view', [
            'subscription' => $this->activeSubscription,
            'latestInvoice' => $this->latestInvoice,
            'subscriptionSummary' => $this->subscriptionSummary,
            'users' => $this->tenantUsers,
            'currency' => Currency::getDefault(),
        ]);
    }
}
