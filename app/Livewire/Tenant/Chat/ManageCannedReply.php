<?php

namespace App\Livewire\Tenant\Chat;

use App\Models\Tenant\CannedReply;
use App\Rules\PurifiedInput;
use App\Services\FeatureService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManageCannedReply extends Component
{
    use WithPagination;

    public CannedReply $canned;

    public $showCannedModal = false;

    public $confirmingDeletion = false;

    public $canned_id = null;

    protected $featureLimitChecker;

    public $tenant_id;

    protected $listeners = [
        'editCannedPage' => 'editCannedPage',
        'confirmDelete' => 'confirmDelete',
    ];

    public function boot(FeatureService $featureLimitChecker)
    {
        $this->featureLimitChecker = $featureLimitChecker;
    }

    public function mount()
    {
        if (! checkPermission('tenant.canned_reply.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }

        $this->resetForm();
        $this->canned = new CannedReply;
        $this->tenant_id = tenant_id();
    }

    public function validateCannedDescription()
    {
        $this->validate([
            'canned.description' => ['required', 'string', new PurifiedInput(t('sql_injection_error'))],
        ]);
    }

    protected function rules()
    {
        return [
            'canned.title' => [
                'required',
                'min:3',
                'max:255',
                Rule::unique('canned_replies', 'title')->where(function ($query) {
                    return $query->where('tenant_id', tenant_id());
                })
                    ->ignore($this->canned->id),
                new PurifiedInput(t('sql_injection_error')),
            ],
            'canned.description' => ['required', new PurifiedInput(t('sql_injection_error'))],
        ];
    }

    public function createCanned()
    {
        if (! $this->showCannedModal) {
            $this->resetForm();
            $this->showCannedModal = true;
        }
    }

    public function save()
    {
        if (checkPermission(['tenant.canned_reply.create', 'tenant.canned_reply.edit'])) {
            $this->validate();

            $isNew = ! $this->canned->exists;
            $this->canned->added_from = Auth::id();

            // For new records, check if creating one more would exceed the limit
            if ($isNew) {
                $limit = $this->featureLimitChecker->getLimit('canned_replies');

                // Skip limit check if unlimited (-1) or no limit set (null)
                if ($limit !== null && $limit !== -1) {
                    $currentCount = CannedReply::where('tenant_id', tenant_id())->count();

                    if ($currentCount >= $limit) {
                        $this->showCannedModal = false;
                        // Show upgrade notification
                        $this->notify([
                            'type' => 'warning',
                            'message' => t('canned_replies_limit_reached_upgrade_plan'),
                        ]);

                        return;
                    }
                }
            }

            if ($this->canned->isDirty()) {
                $this->canned->tenant_id = tenant_id();
                $this->canned->save();

                if ($isNew) {
                    $this->featureLimitChecker->trackUsage('canned_replies');
                }

                $this->showCannedModal = false;
                $message = $this->canned->wasRecentlyCreated
                    ? t('canned_reply_save_successfully')
                    : t('canned_reply_update_successfully');
                $this->notify(['type' => 'success', 'message' => $message]);
                $this->dispatch('pg:eventRefresh-canned-reply-table-ysrvwi-table');
            } else {
                $this->showCannedModal = false;
            }
        }
    }

    public function editCannedPage($cannedId)
    {

        $canned = CannedReply::findOrFail($cannedId);
        $this->canned = $canned;
        $this->resetValidation();
        $this->showCannedModal = true;
    }

    public function confirmDelete($cannedId)
    {
        $this->canned_id = $cannedId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if (checkPermission('tenant.canned_reply.delete')) {
            $cannedReply = CannedReply::find($this->canned_id);
            if ($cannedReply) {
                $cannedReply->delete();
            }

            $this->confirmingDeletion = false;
            $this->resetForm();
            $this->canned_id = null;
            $this->resetPage();

            $this->notify(['type' => 'success', 'message' => t('canned_reply_delete_successfully')]);
            $this->dispatch('pg:eventRefresh-canned-reply-table-ysrvwi-table');
        }
    }

    private function resetForm()
    {
        $this->resetExcept('canned');
        $this->resetValidation();

        $this->canned = new CannedReply;
    }

    public function getRemainingLimitProperty()
    {
        return $this->featureLimitChecker->getRemainingLimit('canned_replies', CannedReply::class);
    }

    public function getTotalLimitProperty()
    {
        return $this->featureLimitChecker->getLimit('canned_replies', CannedReply::class);
    }

    public function getIsUnlimitedProperty()
    {
        return $this->remainingLimit === null;
    }

    public function getHasReachedLimitProperty()
    {
        return $this->featureLimitChecker->hasReachedLimit('canned_replies', CannedReply::class);
    }

    public function render()
    {
        return view('livewire.tenant.chat.manage-canned-reply');
    }
}
