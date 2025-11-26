<?php

namespace App\Livewire\Tenant\Tables;

use App\Enum\Tenant\WhatsAppTemplateRelationType;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\Contact;
use App\Models\Tenant\WhatsappTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class CampaignTable extends PowerGridComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'DESC';

    public bool $showFilters = false;

    public bool $deferLoading = true;

    public string $tableName = 'campaign-table-zfu4eg-table';

    public string $loadingComponent = 'components.custom-loading';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {

        return [
            PowerGrid::header()
                ->withoutLoading()
                ->showToggleColumns()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(perPage: table_pagination_settings()['current'], perPageValues: table_pagination_settings()['options'])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $tenantId = tenant_id();
        $query = Campaign::query()
            ->select([
                'campaigns.*',
                'whatsapp_templates.template_name',
                DB::raw('(SELECT COUNT(*) FROM campaign_details
        WHERE campaign_details.campaign_id = campaigns.id
        AND campaign_details.tenant_id = '.$tenantId.'
        AND message_status = "delivered") as delivered'),
                DB::raw('(SELECT COUNT(*) FROM campaign_details
        WHERE campaign_details.campaign_id = campaigns.id
        AND campaign_details.tenant_id = '.$tenantId.'
        AND message_status = "read") as read_by'),
                DB::raw('ROW_NUMBER() OVER (ORDER BY campaigns.created_at DESC) as row_num'),
                DB::raw('(SELECT COUNT(*) FROM campaign_details
        WHERE campaign_details.campaign_id = campaigns.id
        AND campaign_details.tenant_id = '.$tenantId.') as total_details'),
                DB::raw('(SELECT COUNT(*) FROM campaign_details
        WHERE campaign_details.campaign_id = campaigns.id
        AND campaign_details.tenant_id = '.$tenantId.'
        AND status = 1) as pending_count'),
                DB::raw('(SELECT COUNT(*) FROM campaign_details
        WHERE campaign_details.campaign_id = campaigns.id
        AND campaign_details.tenant_id = '.$tenantId.'
        AND status = 1) as in_queue_count'),
                DB::raw('(SELECT COUNT(*) FROM campaign_details
        WHERE campaign_details.campaign_id = campaigns.id
        AND campaign_details.tenant_id = '.$tenantId.'
        AND message_status = "sent") as executed_count'),
                DB::raw('(SELECT COUNT(*) FROM campaign_details
        WHERE campaign_details.campaign_id = campaigns.id
        AND campaign_details.tenant_id = '.$tenantId.'
        AND message_status = "failed") as failed_count'),
            ])
            ->leftJoin('whatsapp_templates', function ($join) use ($tenantId) {
                $join->on('campaigns.template_id', '=', 'whatsapp_templates.template_id')
                    ->where('whatsapp_templates.tenant_id', '=', $tenantId);
            })
            ->where('campaigns.tenant_id', '=', tenant_id());

        if (checkPermission('tenant.contact.view')) {
            return $query;
        } elseif (checkPermission('tenant.contact.view_own')) {
            $user = Auth::user();

            // Ensure this only applies to tenant staff
            if ($user->user_type === 'tenant' && $user->tenant_id === tenant_id() && $user->is_admin === false) {
                $staffId = $user->id;
                $tenantSubdomain = tenant_subdomain_by_tenant_id($user->tenant_id);
                $contactTable = Contact::fromTenant($tenantSubdomain)->getModel()->getTable();

                return $query->whereExists(function ($subquery) use ($staffId, $contactTable) {
                    $subquery->select(DB::raw(1))
                        ->from('campaign_details')
                        ->join($contactTable, 'campaign_details.rel_id', '=', $contactTable.'.id')
                        ->whereColumn('campaign_details.campaign_id', 'campaigns.id')
                        ->where($contactTable.'.assigned_id', $staffId);
                });
            }
        }

        // Default return if no conditions match
        return $query;
    }

    public function relationSearch(): array
    {
        return [
            'whatsappTemplate' => [
                'template_name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('row_num')
            ->add('name', function ($campaign) {

                $canView = checkPermission('tenant.campaigns.show_campaign');
                $canEdit = checkPermission('tenant.campaigns.edit');
                $canDelete = checkPermission('tenant.campaigns.delete');

                return '<div class="group relative inline-block min-h-[40px]">
                <a class="dark:text-gray-200 text-primary-600 dark:hover:text-primary-400" href="'.tenant_route('tenant.campaigns.details', ['campaignId' => $campaign->id]).'">'.$campaign->name.'</a>
                <!-- Action Links -->
                <div class="absolute left-0 lg:left-0 top-3 mt-2 pt-1 hidden contact-actions space-x-1 text-xs text-gray-600 dark:text-gray-300">'

                    .($canView ? '<a href="'.tenant_route('tenant.campaigns.details', ['campaignId' => $campaign->id]).'" class="hover:text-info-600">'.t('view').'</a>' : '').

                    ($canView && ($canEdit || $canDelete) ? '<span>|</span>' : '').

                    ($canEdit ? '<a href="'.tenant_route('tenant.campaign.edit', ['id' => $campaign->id]).'" class="hover:text-success-600">'.t('edit').'</a>' : '').

                    ($canEdit && $canDelete ? '<span>|</span>' : '').

                    ($canDelete ? '<button onclick="Livewire.dispatch(\'confirmDelete\', { campaignId: '.$campaign->id.' })" class="hover:text-danger-600">'.t('delete').'</button>' : '').
                    '</div>
            </div>';
            })

            ->add('rel_type', function ($templateBot) {
                $class = $templateBot->rel_type == 'lead'
                    ? 'bg-primary-100 text-primary-800 dark:text-primary-400 dark:bg-primary-900/20'
                    : 'bg-success-100 text-success-800 dark:text-success-400 dark:bg-success-900/20';

                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.$class.'">'.ucfirst($templateBot->rel_type).'</span>';
            })
            ->add('sending_count_formatted', function ($campaign) {
                return "<span class='text-sm text-center mx-2'>".$campaign->sending_count.'</span>';
            })
            ->add('delivered', function ($campaign) {
                return "<span class='text-sm text-center mx-5'>".$campaign->delivered.'</span>';
            })
            ->add('read_by', function ($campaign) {
                return "<span class='text-sm text-center mx-4'>".$campaign->read_by.'</span>';
            })
            ->add('status_badge', function ($campaign) {
                $total = $campaign->total_details ?? 0;
                $executed = $campaign->executed_count ?? 0;
                $failed = $campaign->failed_count ?? 0;
                $pending = $campaign->pending_count ?? 0;
                $inQueue = $campaign->in_queue_count ?? 0;
                $isSent = $campaign->is_sent ?? 0;

                if ($total == 0) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400">'.t('no_data').'</span>';
                }

                // Check if campaign is sent and processing is complete
                if ($isSent && $inQueue == 0) {
                    if ($executed > 0) {
                        // At least some messages sent successfully
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/20 dark:text-success-400">'.t('executed').'</span>';
                    } elseif ($failed == $total) {
                        // All messages failed
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900/20 dark:text-danger-400">'.t('failed').'</span>';
                    } else {
                        // Fallback for edge cases
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/20 dark:text-warning-400">'.t('processing').'</span>';
                    }
                }

                // Campaign not sent yet - check queue status
                if ($inQueue > 0) {
                    if (($executed + $pending) == 0) {
                        // All in queue - nothing processed yet
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/20 dark:text-warning-400">'.t('pending').'</span>';
                    } else {
                        // Some processed + some in queue - processing
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info-100 text-info-800 dark:bg-info-900/20 dark:text-info-400">'.t('in_process').'</span>';
                    }
                } else {
                    // No items in queue - processing complete
                    if ($executed > 0) {
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/20 dark:text-success-400">'.t('executed').'</span>';
                    } else {
                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900/20 dark:text-danger-400">'.t('failed').'</span>';
                    }
                }
            })
            ->add('created_at_formatted', function ($campaign) {
                return '<div class="relative group"><span class="cursor-default"  data-tippy-content="'.format_date_time($campaign->created_at).'">'.\Carbon\Carbon::parse($campaign->created_at)->diffForHumans(['options' => \Carbon\Carbon::JUST_NOW]).'</span>
                        </div>';
            });
    }

    public function columns(): array
    {
        return [
            Column::make(t('SR.NO'), 'row_num')
                ->sortable(),

            Column::make(t('campaign_name'), 'name')
                ->sortable()
                ->searchable(),

            Column::make(t('template'), 'template_name')
                ->sortable()
                ->searchable(),

            Column::make(t('relation_type'), 'rel_type')
                ->sortable()
                ->searchable(),

            Column::make(t('status'), 'status_badge')
                ->sortable(false)
                ->searchable(false),

            Column::make(t('total'), 'sending_count_formatted', 'sending_count')
                ->sortable()
                ->searchable(),

            Column::make(t('delivered_to'), 'delivered')
                ->sortable(),

            Column::make(t('ready_by'), 'read_by')
                ->sortable(),

            Column::make(t('created_at'), 'created_at_formatted', 'created_at')
                ->sortable()
                ->searchable(),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::select('template_name', 'whatsapp_templates.template_id')
                ->dataSource(
                    WhatsappTemplate::query()
                        ->select([
                            'template_id',
                            'template_name',
                        ])
                        ->where('tenant_id', tenant_id())
                        ->get()
                        ->toArray()
                )
                ->optionValue('template_id')
                ->optionLabel('template_name'),

            Filter::select('rel_type', 'campaigns.rel_type')
                ->dataSource(collect(WhatsAppTemplateRelationType::getRelationtype())
                    ->map(fn ($value, $key) => ['value' => $key, 'label' => ucfirst($value)])
                    ->values()
                    ->toArray())
                ->optionValue('value')
                ->optionLabel('label'),

            Filter::datepicker('created_at', 'campaigns.created_at'),

        ];
    }
}
