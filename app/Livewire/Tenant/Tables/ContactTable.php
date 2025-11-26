<?php

namespace App\Livewire\Tenant\Tables;

use App\Enum\Tenant\WhatsAppTemplateRelationType;
use App\Facades\TenantCache;
use App\Models\Tenant\Contact;
use App\Models\Tenant\CustomField;
use App\Models\Tenant\Group;
use App\Models\Tenant\Source;
use App\Models\Tenant\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class ContactTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'contact-table';

    public bool $deferLoading = false;

    public bool $showFilters = false;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public string $loadingComponent = 'components.custom-loading';

    public array $selected = [];

    public $tenant_id;

    public $tenant_subdomain;

    protected ?array $customFields = null;

    protected const CACHE_KEY_USERS = 'contacts_table_users_for_filter';

    protected const CACHE_KEY_CUSTOM_FIELDS = 'contacts_table_custom_fields';

    protected const CACHE_KEY_STATUSES = 'contacts_table_statuses_for_filter';

    protected const CACHE_KEY_SOURCES = 'contacts_table_sources_for_filter';

    protected const CACHE_KEY_GROUPS = 'contacts_table_groups_for_filter';

    protected const CACHE_DURATION = 600;

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
        $this->tenant_id = tenant_id();
        $this->tenant_subdomain = tenant_subdomain_by_tenant_id($this->tenant_id);
    }

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable('contacts-list')
                ->striped()
                ->stripTags(true)
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            PowerGrid::header()
                ->showToggleColumns()
                ->withoutLoading()
                ->showSearchInput(),

            PowerGrid::footer()
                ->showPerPage(perPage: table_pagination_settings()['current'], perPageValues: table_pagination_settings()['options'])
                ->showRecordCount(),
        ];
    }

    public function header(): array
    {
        $buttons = [];

        $defaultPositionClassBulkDelete = 'static sm:absolute md:absolute lg:absolute lg:top-[9.5rem] md:top-[9.8rem] sm:top-[9.5rem] sm:left-[207px] md:left-[195px] lg:left-[183px] mb-2 sm:mb-0 me-1';
        $defaultPositionClassInitiateChat = 'static sm:absolute md:absolute lg:absolute md:top-[9.8rem] sm:top-[9.5rem] sm:left-[336px] md:left-[322px] lg:left-[310px] lg:top-[9.5rem] mb-2 sm:mb-0';

        $alternativePositionClassBulkDelete = 'static sm:absolute md:absolute lg:absolute md:top-[5.0rem] sm:top-[5.0rem] sm:left-[210px] md:left-[211px] lg:left-[185px] mb-2 sm:mb-0 me-1';
        $alternativePositionClassInitiateChat = 'static sm:absolute md:absolute lg:absolute md:top-[7.0rem] sm:top-[7.0rem] sm:left-[190px] md:left-[195px] mb-2 sm:mb-0';

        if (checkPermission(['tenant.contact.create', 'tenant.contact.bulk_import'])) {
            $bulkDeleteClass = $defaultPositionClassBulkDelete;
            $initiateChatClass = $defaultPositionClassInitiateChat;
        } else {
            $bulkDeleteClass = $alternativePositionClassBulkDelete;
            $initiateChatClass = $alternativePositionClassInitiateChat;
        }

        $contactCount = Contact::fromTenant($this->tenant_subdomain)->count();

        if (checkPermission('tenant.contact.delete')) {
            if ($contactCount > 0) {
                $buttons[] = Button::add('bulk-delete')
                    ->id()
                    ->slot(t('bulk_delete').'(<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                    ->class("iinline-flex items-center justify-center px-3 py-2 text-sm border border-transparent rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-red-600 text-white hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 whitespace-nowrap $bulkDeleteClass")
                    ->dispatch('bulkDelete.'.$this->tableName, []);
            }
        }
        if (checkPermission('tenant.contact.create', 'tenant.contact.edit')) {
            if ($contactCount > 0) {
                $buttons[] = Button::add('initiate-chat')
                    ->id()
                    ->slot(t('initiate_chat').'(<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                    ->class("inline-flex items-center justify-center px-3 py-2 text-sm border border-transparent rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-indigo-600 text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 whitespace-nowrap $initiateChatClass")
                    ->dispatch('bulkInitiateChat.'.$this->tableName, []);
            }
        }

        return $buttons;
    }

    protected function loadCustomFields(): void
    {
        $key = self::CACHE_KEY_CUSTOM_FIELDS.$this->tenant_id;

        $this->customFields = Cache::remember($key, 3600, function () {
            return CustomField::where('tenant_id', $this->tenant_id)
                ->where('is_active', true)
                ->where('show_on_table', true)
                ->orderBy('field_name')
                ->get()
                ->toArray();
        });
    }

    protected function getCustomFieldValue($contact, $field): string
    {
        $customData = $contact->custom_fields_data ?? [];
        $value = $customData[$field['field_name']] ?? '';

        // Format the value based on field type
        switch ($field['field_type']) {
            case 'date':
                return $value ? Carbon::parse($value)->format('Y-m-d') : '';
            case 'checkbox':
                if (is_array($value)) {
                    return implode(', ', array_filter($value));
                }

                return '';
            case 'dropdown':
                return (string) $value;
            default:
                return (string) $value;
        }
    }

    protected function getCustomFieldRawValue($contact, $field)
    {
        $customData = $contact->custom_fields_data ?? [];

        return $customData[$field['field_name']] ?? null;
    }

    public function datasource(): Builder
    {
        $table_name = $this->tenant_subdomain.'_contacts';
        $query = Contact::fromTenant($this->tenant_subdomain)
            ->where('tenant_id', $this->tenant_id)
            ->selectRaw('*, (SELECT COUNT(*) FROM '.$table_name.' i2 WHERE i2.id <= '.$table_name.'.id AND i2.tenant_id = ?) as row_num', [$this->tenant_id])
            ->with([
                'user:id,firstname,lastname,avatar',
                'status:id,name,color',
                'source:id,name',
            ]);

        // Filter contacts based on permissions
        if (checkPermission('tenant.contact.view')) {
            return $query; // all contacts
        } elseif (checkPermission('tenant.contact.view_own')) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user && $user->user_type === 'tenant' && $user->tenant_id === tenant_id() && $user->is_admin === false) {
                $staffId = $user->id;

                return $query->where('assigned_id', $staffId);
            }
        }

        // Default return if no permissions match
        return $query;
    }

    public function relationSearch(): array
    {
        return [
            'user' => [
                'firstname',
                'lastname',
            ],
            'status' => [
                'name',
            ],
            'source' => [
                'name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        if ($this->customFields === null) {
            $this->loadCustomFields();
        }

        $fields = PowerGrid::fields()
            ->add('row_num')
            ->add('firstname', function ($contact) {
                return view('components.contacts.name-with-actions', [
                    'id' => $contact->id,
                    'fullName' => $contact->firstname.' '.$contact->lastname,
                ])->render();
            })
            ->add('firstname_raw', fn ($contact) => e($contact->firstname.' '.$contact->lastname))
            ->add('status_id', function ($contact) {
                if (! empty($contact->status->color)) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    style="background-color: '.e($contact->status->color).'20; color: '.e($contact->status->color).';">
                    '.e($contact->status->name).'</span>';
                }

                return '<span class="text-gray-400">No Status</span>';
            })
            ->add('assigned_id', function ($contact) {
                if (! $contact->user) {
                    return t('not_assigned');
                }

                $profileImage = ! empty($contact->user->avatar) && Storage::disk('public')->exists($contact->user->avatar)
                    ? Storage::url($contact->user->avatar)
                    : asset('img/user-placeholder.jpg');

                $fullName = e($contact->user->firstname.' '.$contact->user->lastname);

                return '<div class="relative group flex items-center cursor-pointer">
                    <a href="'.tenant_route('tenant.staff.details', ['staffId' => $contact->assigned_id]).'">
                        <img src="'.$profileImage.'"
                            class="w-9 h-9 rounded-full mx-3 object-cover"
                            data-tippy-content="'.$fullName.'">
                    </a>
                </div>';
            })->add('initiate_chat', function ($contact) {
                return '
                <button
                    x-data
                    @click="$dispatch(\'initiateChat\', { id: '.$contact->id.' })"
                    class="inline-flex items-center text-green-500  hover:text-green-700"
                     data-tippy-content="Initiate Chat"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 32 32" class="h-5 w-5">
                        <path d="M16.01 2.006a13.97 13.97 0 00-12.2 20.96L2 30l7.2-1.8A13.974 13.974 0 1016.01 2.006zm0 25.974c-2.08 0-4.07-.53-5.83-1.53l-.42-.24-4.28 1.07 1.1-4.16-.28-.43A11.96 11.96 0 1116.01 28zm6.41-8.94c-.34-.17-2.01-.99-2.33-1.1-.31-.11-.54-.17-.76.17-.23.34-.88 1.1-1.08 1.32-.2.23-.4.25-.75.08-.34-.17-1.44-.53-2.74-1.7a10.182 10.182 0 01-1.89-2.33c-.2-.34 0-.52.15-.69.15-.16.34-.4.5-.6.17-.2.23-.34.34-.56.12-.23.06-.43 0-.6-.07-.17-.76-1.84-1.04-2.52-.28-.68-.56-.59-.76-.6h-.65c-.22 0-.56.08-.85.4s-1.12 1.1-1.12 2.68 1.15 3.1 1.31 3.32c.17.23 2.27 3.45 5.5 4.83.77.33 1.37.53 1.83.68.77.24 1.46.2 2.01.12.61-.09 1.87-.76 2.13-1.5.27-.74.27-1.37.19-1.5-.07-.13-.3-.2-.63-.36z"/>
                    </svg>
                </button>
            ';
            })
            ->add('source_id', fn ($contact) => $contact->source?->name ?? 'N/A')
            ->add('group_id', function ($contact) {
                try {
                    // ✅ SIMPLIFIED: Use the reliable helper method
                    $groupIds = $contact->getGroupIds();

                    if (empty($groupIds)) {
                        return '<span class="text-gray-400 text-sm">No groups</span>';
                    }

                    // Get up to 3 groups for display
                    $displayLimit = 3;
                    $displayGroupIds = array_slice($groupIds, 0, $displayLimit);

                    $groups = Group::whereIn('id', $displayGroupIds)
                        ->where('tenant_id', $this->tenant_id)
                        ->select(['id', 'name'])
                        ->get();

                    if ($groups->isEmpty()) {
                        return '<span class="text-orange-500 text-sm">Groups not found</span>';
                    }

                    $html = '<div class="flex flex-wrap gap-1">';

                    foreach ($groups as $group) {
                        $html .= '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-info-100 text-info-800">'
                            .e($group->name).'</span>';
                    }

                    // Show count if there are more groups
                    $totalGroups = count($groupIds);
                    if ($totalGroups > $displayLimit) {
                        $remaining = $totalGroups - $displayLimit;
                        $html .= '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">+'
                            .$remaining.' more</span>';
                    }

                    $html .= '</div>';

                    return $html;
                } catch (\Exception $e) {
                    return '<span class="text-danger-500 text-sm">Display error</span>';
                }
            })
            ->add('group_names', function ($contact) {
                try {
                    $groupIds = $contact->getGroupIds();

                    if (empty($groupIds)) {
                        return '';
                    }

                    $groups = Group::whereIn('id', $groupIds)
                        ->where('tenant_id', $this->tenant_id)
                        ->pluck('name')
                        ->toArray();

                    return implode(', ', $groups);
                } catch (\Exception $e) {
                    return '';
                }
            })
            ->add('created_at_formatted', function ($contact) {
                return '<div class="relative group">
                        <span class="cursor-default" data-tippy-content="'.format_date_time($contact->created_at).'">'
                    .Carbon::parse($contact->created_at)->diffForHumans(['options' => Carbon::JUST_NOW]).'</span>
                    </div>';
            })
            ->add('type', function ($contact) {
                return t($contact->type);
            });

        // Add custom field values
        foreach ($this->customFields as $field) {
            // Add both formatted and raw values for proper sorting
            $fields = $fields->add('custom_'.$field['field_name'], function ($contact) use ($field) {
                $rawValue = $this->getCustomFieldRawValue($contact, $field);
                $formattedValue = $this->getCustomFieldValue($contact, $field);

                // For date fields, add data attribute for proper sorting
                if ($field['field_type'] === 'date' && $rawValue) {
                    return sprintf(
                        '<span data-date="%s">%s</span>',
                        e($rawValue),
                        e($formattedValue)
                    );
                }

                // For number fields, right-align and format
                if ($field['field_type'] === 'number' && is_numeric($rawValue)) {
                    return sprintf(
                        '<span class="block text-right" data-number="%s">%s</span>',
                        e($rawValue),
                        number_format($rawValue)
                    );
                }

                return e($formattedValue);
            });
        }

        return $fields;
    }

    public function columns(): array
    {
        if ($this->customFields === null) {
            $this->loadCustomFields();
        }

        $columns = [
            Column::make(t('SR.NO'), 'row_num')
                ->sortable(),

            Column::make(t('name'), 'firstname')
                ->bodyAttribute('class="relative"')
                ->sortable()
                ->searchable()
                ->visibleInExport(false),

            Column::make(t('name'), 'firstname_raw')
                ->hidden()
                ->visibleInExport(true),

            Column::make(t('type'), 'type')
                ->sortable()
                ->searchable(),

            Column::make(t('phone'), 'phone')
                ->sortable()
                ->searchable(),

            Column::make(t('assigned'), 'assigned_id')
                ->sortable()
                ->searchable(),

            Column::make(t('initiate_chat'), 'initiate_chat'),

            Column::make(t('status'), 'status_id')
                ->sortable()
                ->searchable(),

            Column::make(t('source'), 'source_id')
                ->sortable()
                ->searchable(),

            Column::make(t('group'), 'group_id')->sortable(false)->searchable(false)->visibleInExport(false),

            Column::make(t('groups'), 'group_names')->hidden()->visibleInExport(true),

            Column::make(t('active'), 'is_enabled')
                ->sortable()
                ->toggleable(hasPermission: true, trueLabel: '1', falseLabel: '0'),
        ];

        // Add custom field columns
        foreach ($this->customFields as $field) {
            $col = Column::make($field['field_label'], 'custom_'.$field['field_name'])
                ->searchable();

            // Handle sorting based on field type
            if (in_array($field['field_type'], ['text', 'textarea', 'number', 'date', 'dropdown'])) {
                $col->sortable();
            }

            // Add appropriate CSS classes based on field type
            $classes = match ($field['field_type']) {
                'number' => 'text-right',
                'date' => 'whitespace-nowrap',
                default => ''
            };

            if ($classes) {
                $col->bodyAttribute("class=\"$classes\"");
            }

            $columns[] = $col;
        }

        $columns[] = Column::make(t('created_at'), 'created_at_formatted', 'created_at')
            ->sortable();

        return $columns;
    }

    public function filters(): array
    {
        if ($this->customFields === null) {
            $this->loadCustomFields();
        }

        $filters = [
            // Type filter
            Filter::select('type')
                ->dataSource(collect(WhatsAppTemplateRelationType::getRelationtype())
                    ->map(fn ($value, $key) => ['value' => $key, 'label' => ucfirst($value)])
                    ->values()->toArray())
                ->optionValue('value')->optionLabel('label'),

            Filter::select('assigned_id')
                ->dataSource(TenantCache::remember(self::CACHE_KEY_USERS, self::CACHE_DURATION, function () {
                    return User::query()->select(['id', 'firstname', 'lastname'])->where('tenant_id', $this->tenant_id)->get()
                        ->map(fn ($user) => ['id' => $user->id, 'name' => $user->firstname.' '.$user->lastname]);
                }, ['contact-filters', 'users']))
                ->optionValue('id')->optionLabel('name'),

            Filter::select('status_id')
                ->dataSource(TenantCache::remember(self::CACHE_KEY_STATUSES.'_'.$this->tenant_subdomain, self::CACHE_DURATION, function () {
                    return Status::where('tenant_id', $this->tenant_id)->select(['id', 'name'])->get()->toArray();
                }, ['contact-filters', 'statuses']))
                ->optionValue('id')->optionLabel('name'),

            Filter::select('source_id')
                ->dataSource(TenantCache::remember(self::CACHE_KEY_SOURCES.'_'.$this->tenant_subdomain, self::CACHE_DURATION, function () {
                    return Source::where('tenant_id', $this->tenant_id)->select(['id', 'name'])->get()->toArray();
                }, ['contact-filters', 'sources']))
                ->optionValue('id')->optionLabel('name'),

            Filter::select('group_id', 'group_filter')
                ->dataSource(TenantCache::remember(self::CACHE_KEY_GROUPS.'_'.$this->tenant_subdomain, self::CACHE_DURATION, function () {
                    return Group::where('tenant_id', $this->tenant_id)->select(['id', 'name'])->get()->toArray();
                }, ['contact-filters', 'groups']))
                ->optionValue('id')->optionLabel('name')
                ->builder(function (Builder $builder, string $value) {
                    return $builder->whereJsonContains('group_id', (int) $value);
                }),
        ];

        return $filters;
    }

    #[On('bulkDelete.{tableName}')]
    public function bulkDelete(): void
    {
        $selectedIds = $this->checkboxValues;
        if (! empty($selectedIds) && count($selectedIds) !== 0) {
            $this->dispatch('confirmDelete', $selectedIds);
            $this->checkboxValues = [];
        } else {
            $this->notify(['type' => 'danger', 'message' => t('no_contact_selected')]);
        }
    }

    public function onUpdatedToggleable(string $id, string $field, string $value): void
    {
        if (checkPermission('tenant.contact.edit')) {
            $this->dispatch('refreshComponent');

            Contact::fromTenant($this->tenant_subdomain)->where('id', $id)->update(['is_enabled' => $value === '1' ? 1 : 0]);

            $this->notify([
                'message' => $value === '1' ? t('contact_enable_successfully') : t('contact_disabled_successfully'),
                'type' => 'success',
            ]);
        } else {
            $this->notify([
                'message' => t('access_denied_note'),
                'type' => 'warning',
            ]);
        }
    }

    #[On('bulkInitiateChat.{tableName}')]
    public function bulkInitiateChat(): void
    {

        $selectedIds = $this->checkboxValues;
        if (! empty($selectedIds) && count($selectedIds) !== 0) {
            $this->dispatch('bulkInitiateChatSending', $selectedIds);
            $this->checkboxValues = [];
        } else {
            $this->notify(['type' => 'danger', 'message' => t('no_contact_selected')]);
        }
    }
}
