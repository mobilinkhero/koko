<?php

namespace App\Livewire\Tenant\Tables;

use App\Models\Tenant\ContactImport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class ImportContactsLogs extends PowerGridComponent
{
    public string $tableName = 'import-contacts-logs-9fmmbv-table';

    public bool $showUpdateMessages = true;

    protected $listeners = [
        'refreshImportLogsTable' => '$refresh',
    ];

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return ContactImport::query()
            ->where('tenant_id', tenant_id())
            ->orderBy('created_at', 'desc');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('status')
            ->add('file_name', function ($import) {
                $fileName = basename($import->file_path);

                return "
                    <button
                        wire:click=\"\$dispatch('downloadFile', { importId: {$import->id} })\"
                        class=\"text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 hover:underline\"
                        title=\"".t('download_file')."\">
                        <div class=\"inline-flex items-center space-x-1\">
                            <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10\" />
                            </svg>
                            <span>{$fileName}</span>
                        </div>
                    </button>
                ";
            })
            ->add('total_records')
            ->add('processed_records')
            ->add('valid_records')
            ->add('invalid_records')
            ->add('skipped_records')
            ->add('progress_percentage', function ($import) {
                if ($import->total_records > 0) {
                    return round(($import->processed_records / $import->total_records) * 100, 1);
                }

                return 0;
            })
            ->add('created_at_formatted', function ($contact) {
                return '<div class="relative group">
                        <span class="cursor-default" data-tippy-content="'.format_date_time($contact->created_at).'">'
                    .Carbon::parse($contact->created_at)->diffForHumans(['options' => Carbon::JUST_NOW]).'</span>
                    </div>';
            })
            ->add('status_badge', function (ContactImport $import) {
                $statusColors = [
                    ContactImport::STATUS_PROCESSING => 'yellow',
                    ContactImport::STATUS_COMPLETED => 'green',
                    ContactImport::STATUS_FAILED => 'red',
                ];

                $color = $statusColors[$import->status] ?? 'gray';
                $statusText = ucfirst($import->status);

                return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{$color}-100 text-{$color}-800 dark:bg-{$color}-900 dark:text-{$color}-200'>
                    {$statusText}
                </span>";
            })
            ->add('progress_bar', function (ContactImport $import) {
                if ($import->total_records > 0) {
                    $percentage = ($import->processed_records / $import->total_records) * 100;
                    $colorClass = match ($import->status) {
                        ContactImport::STATUS_COMPLETED => 'bg-green-600',
                        ContactImport::STATUS_FAILED => 'bg-red-600',
                        default => 'bg-blue-600'
                    };

                    return "
                        <div class='flex items-center space-x-2'>
                            <div class='flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2'>
                                <div class='h-2 rounded-full {$colorClass}' style='width: {$percentage}%'></div>
                            </div>
                            <span class='text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap'>
                                {$import->processed_records}/{$import->total_records}
                            </span>
                        </div>
                    ";
                }

                return '<span class="text-xs text-gray-500">-</span>';
            })
            ->add('records_summary', function (ContactImport $import) {
                return "
                    <div class='flex flex-wrap gap-1 text-xs'>
                        <span class='inline-flex items-center px-1.5 py-0.5 rounded text-green-700 bg-green-100 dark:bg-green-900/20 dark:text-green-400'>
                            ✓ {$import->valid_records}
                        </span>
                        ".($import->invalid_records > 0 ? "
                        <span class='inline-flex items-center px-1.5 py-0.5 rounded text-red-700 bg-red-100 dark:bg-red-900/20 dark:text-red-400'>
                            ✗ {$import->invalid_records}
                        </span>" : '').'
                        '.($import->skipped_records > 0 ? "
                        <span class='inline-flex items-center px-1.5 py-0.5 rounded text-yellow-700 bg-yellow-100 dark:bg-yellow-900/20 dark:text-yellow-400'>
                            ⚠ {$import->skipped_records}
                        </span>" : '').'
                    </div>
                ';
            });
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->searchable(),

            Column::make('Status', 'status_badge', 'status')
                ->sortable(),

            Column::make('File', 'file_name')
                ->sortable()
                ->searchable(),

            Column::make('Progress', 'progress_bar')
                ->sortable('processed_records'),

            Column::make('Records', 'records_summary')
                ->sortable('valid_records'),

            Column::make('Created', 'created_at_formatted', 'created_at_formatted')
                ->sortable(),

            Column::action('Actions'),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(ContactImport $row): array
    {
        return [
            Button::add('view')
                ->slot('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>')
                ->class('inline-flex items-center justify-center w-8 h-8 text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-lg transition-colors')
                ->tooltip('View Details')
                ->dispatch('showImportDetails', ['importId' => $row->id]),

            Button::add('delete')
                ->slot('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>')
                ->class('inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors')
                ->tooltip('Delete Import')
                ->dispatch('confirmDeleteImport', ['importId' => $row->id]),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Actions Method
    |--------------------------------------------------------------------------
    | Enable the method below only if the Routes below are defined:
    |
    */

    /*
    public function actionRules(ContactImport $row): array
    {
       return [
            // Hide buttons edit and delete when dishes is ID 1
            Rule::button('edit')
                ->when(fn(ContactImport $dish) => $dish->id === 1)
                ->hide(),
        ];
    }
    */
}
