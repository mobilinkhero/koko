<div class="px-4 md:px-0">
    <x-slot:title>
        {{ t('activity_log_details') }}
    </x-slot:title>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
        <x-card class="self-start">
            <x-slot:header>
                <x-settings-heading>
                    {{ t('activity_log_details') }}
                </x-settings-heading>
            </x-slot:header>
            <x-slot:content>
                <div
                    class="flex justify-between p-2 bg-slate-100 dark:bg-gray-800 text-gray-500 dark:text-gray-300 font-normal text-sm">
                    <p> {{ t('action') }} </p>
                    <p>{{ t($data->category) }}</p>
                </div>
                <div class="flex justify-between p-2 mb-4 text-gray-500 dark:text-gray-300 font-normal text-sm">
                    <p> {{ t('date') }} </p>
                    <p>{{ format_date_time($data->created_at) }}</p>
                </div>
                <div class="p-2">
                    <h5 class="mb-2 text-gray-800 dark:text-gray-200"> {{ t('total_parameter') }} </h5>
                    <div
                        class="bg-slate-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm rounded-md p-4 border border-gray-300 dark:border-gray-700 overflow-x-auto">
                        <pre id="json1" class="font-mono text-sm leading-relaxed">
                        </pre>
                    </div>
                </div>
            </x-slot:content>
        </x-card>

        <x-card class="self-start">
            <x-slot:header>
                <x-settings-heading>
                    {{ t('header') }}
                </x-settings-heading>
            </x-slot:header>
            <x-slot:content>
                <div
                    class="grid grid-cols-2 gap-2 border-y p-2 bg-slate-100 dark:bg-gray-800 text-gray-500 dark:text-gray-300 dark:border-gray-700 break-words break-all font-normal text-sm">
                    <p> {{ t('number_id_of_the_whatsapp') }} </p>
                    <p>{{ $data->phone_number_id }}</p>
                </div>
                <div
                    class="grid grid-cols-2 gap-2 border-y p-2 text-gray-500 dark:text-gray-300 dark:border-gray-700 break-words break-all font-normal text-sm">
                    <p> {{ t('business_account_id') }} </p>
                    <p>{{ $data->business_account_id }}</p>
                </div>
                <div
                    class="grid grid-cols-2 gap-2 border-y p-2 bg-slate-100 dark:bg-gray-800 text-gray-500 dark:text-gray-300 dark:border-gray-700 break-words break-all font-normal text-sm">
                    <p> {{ t('whatsapp_access_token') }} </p>
                    <p class="whitespace-normal text-sm flex">{{ $data->access_token }}</p>
                </div>
            </x-slot:content>
        </x-card>

        <x-card class="self-start">
            <x-slot:header>
                <div class="flex flex-col sm:flex-row items-start sm:justify-between sm:items-center mb-3">
                    <x-settings-heading>
                        {{ t('raw_content') }}
                    </x-settings-heading>
                    <span
                        class="bg-info-100 dark:bg-info-900 text-info-600 dark:text-info-300 text-sm font-medium px-2 py-1 mt-2 sm:mt-0 rounded">
                        {{ t('format_type') }}
                    </span>
                </div>
            </x-slot:header>
            <x-slot:content>
                <div class="bg-white dark:bg-gray-900 border dark:border-gray-700 rounded-md">
                    <div
                        class="bg-slate-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm rounded-md p-4 overflow-x-auto">
                        <pre id="raw" class="font-mono text-sm leading-relaxed">
                        </pre>
                    </div>
                </div>
            </x-slot:content>
        </x-card>

        <x-card class="self-start">
            <x-slot:header>
                <div class="flex flex-col sm:flex-row items-start sm:justify-between sm:items-center mb-3">
                    <x-settings-heading>
                        {{ t('response') }}
                    </x-settings-heading>
                    <span
                        class="bg-info-100 dark:bg-info-900 text-info-600 dark:text-info-300 text-sm font-medium px-2 py-1 mt-2 sm:mt-0 rounded">
                        {{ t('response_code') }} : {{ $data->response_code }}
                    </span>
                </div>
            </x-slot:header>
            <x-slot:content>
                <div
                    class="bg-slate-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-sm border border-gray-300 dark:border-gray-700 rounded-md p-4 overflow-x-auto">
                    <pre id="datas" class="font-mono text-sm leading-relaxed">
                    </pre>
                </div>
            </x-slot:content>
        </x-card>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dynamicData = {
            response: {!! json_encode($data->category_params, JSON_PRETTY_PRINT) !!},
            category: {!! json_encode($data->response_data, JSON_PRETTY_PRINT) !!},
            raw: {!! json_encode($data->raw_data, JSON_PRETTY_PRINT) !!}
        };
        preety(dynamicData);
    });
</script>