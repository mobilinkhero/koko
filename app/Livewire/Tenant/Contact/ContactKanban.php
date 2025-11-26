<?php

namespace App\Livewire\Tenant\Contact;

use App\Models\Tenant\Contact;
use App\Models\Tenant\Status;
use App\Traits\WithTenantContext;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ContactKanban extends Component
{
    use WithTenantContext;

    public $statuses = [];

    public $records = [];

    public $tenant_id;

    public $tenant_subdomain;

    protected $listeners = [
        'moveRecord' => 'moveRecord',
        'refreshKanban' => '$refresh',
        'viewContact' => 'viewContact',
        'initiateChat' => 'initiateChat',
    ];

    public function boot()
    {
        $this->bootWithTenantContext();
    }

    public function mount()
    {
        if (! checkPermission(['tenant.contact.view', 'tenant.contact.view_own'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect()->to(tenant_route('tenant.dashboard'));
        }

        $this->tenant_id = tenant_id();
        $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);
        $this->loadData();
    }

    public function loadData()
    {
        // Load statuses dynamically from database
        $this->statuses = Status::where('tenant_id', $this->tenant_id)
            ->orderBy('id')
            ->get()
            ->map(function ($status) {
                return [
                    'id' => $status->id,
                    'title' => $status->name,
                    'color' => $status->color,
                ];
            })
            ->toArray();

        // Load contacts grouped by status
        $contacts = $this->getContactsQuery()->get();

        $this->records = [];
        foreach ($this->statuses as $status) {
            $this->records[$status['id']] = $contacts
                ->where('status_id', $status['id'])
                ->take(20) // Limit to 20 per column for performance
                ->map(function ($contact) {
                    return [
                        'id' => $contact->id,
                        'title' => $contact->firstname.' '.$contact->lastname,
                        'content' => [
                            'company' => $contact->company,
                            'phone' => $contact->phone,
                            'email' => $contact->email,
                            'type' => $contact->type,
                            'assigned' => $contact->user ? $contact->user->firstname.' '.$contact->user->lastname : null,
                        ],
                    ];
                })
                ->toArray();
        }
    }

    protected function getContactsQuery(): Builder
    {
        $query = Contact::fromTenant($this->tenant_subdomain)
            ->where('tenant_id', $this->tenant_id)
            ->with(['user:id,firstname,lastname', 'status:id,name,color'])
            ->orderBy('created_at', 'desc');

        // Apply permission-based filtering
        if (checkPermission('tenant.contact.view')) {
            return $query; // all contacts
        } elseif (checkPermission('tenant.contact.view_own')) {
            $user = auth()->user();
            if ($user->user_type === 'tenant' && $user->tenant_id === tenant_id() && $user->is_admin === false) {
                return $query->where('assigned_id', $user->id);
            }
        }

        return $query;
    }

    public function moveRecord($recordId, $newStatusId)
    {
        if (! checkPermission('tenant.contact.edit')) {
            $this->notify([
                'type' => 'danger',
                'message' => t('access_denied_note'),
            ]);

            return;
        }

        try {
            $contact = Contact::fromTenant($this->tenant_subdomain)->findOrFail($recordId);
            $oldStatusId = $contact->status_id;

            // Assign the new status
            $contact->status_id = $newStatusId;

            // Check if status_id is actually changed (dirty)
            if ($contact->isDirty('status_id')) {
                $contact->last_status_change = now();
                $contact->save();

                // Trigger hooks for status change
                do_action('contact.status_changed', $contact, $oldStatusId, $newStatusId);

                $this->notify([
                    'type' => 'success',
                    'message' => t('contact_status_updated_successfully'),
                ]);

                // Refresh the kanban board
                $this->loadData();
            }
        } catch (\Exception $e) {
            logger()->error('Error updating contact status: '.$e->getMessage());

            $this->notify([
                'type' => 'danger',
                'message' => t('something_went_wrong'),
            ]);
        }
    }

    public function editContact($contactId)
    {
        $this->redirect(tenant_route('tenant.contacts.save', ['contactId' => $contactId]));
    }

    public function viewContact($contactId)
    {
        // Dispatch to parent component to handle modal
        $this->dispatch('openViewModal', $contactId)->to(ContactList::class);
    }

    public function initiateChat($id)
    {
        // Dispatch to parent component to handle modal
        $this->dispatch('initiateChat', $id)->to(ContactList::class);
    }

    public function loadMoreRecords($statusId)
    {
        // Load more records for infinite scrolling
        $existingCount = count($this->records[$statusId] ?? []);

        $moreContacts = $this->getContactsQuery()
            ->where('status_id', $statusId)
            ->skip($existingCount)
            ->take(20)
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'title' => $contact->firstname.' '.$contact->lastname,
                    'content' => [
                        'company' => $contact->company,
                        'phone' => $contact->phone,
                        'email' => $contact->email,
                        'type' => $contact->type,
                        'assigned' => $contact->user ? $contact->user->firstname.' '.$contact->user->lastname : null,
                    ],
                ];
            })
            ->toArray();

        $this->records[$statusId] = array_merge($this->records[$statusId] ?? [], $moreContacts);
    }

    public function render()
    {
        return view('livewire.tenant.contact.contact-kanban');
    }
}
