<x-app-layout>
    <div>
          <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('tickets'), 'route' => tenant_route('tenant.tickets.index')],
        ['label' => t('create_support_ticket') ]
        ]" />

        <div class="mx-auto py-3">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Ticket Form -->
                <div class="lg:col-span-2">
                    <div
                        class="bg-white ring-1 ring-slate-300 rounded-lg dark:bg-transparent dark:ring-slate-600  shadow-sm w-ful">
                        <div class="border-b border-slate-300 px-4 py-3 sm:px-6 dark:border-slate-600">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                    <x-heroicon-o-document-text class="w-6 h-6 text-primary-600 " />
                                </div>
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-300">
                                        {{ t('ticket_details') }}
                                    </h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        {{ t('fill_form_submit_support_request') }}</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            @if (isset($ticket))
                            <livewire:tickets::client.ticket-form :ticket="$ticket" />
                            @else
                            <livewire:tickets::client.ticket-form />
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Enhanced Guidelines Sidebar -->
                <div class="space-y-6">
                    <!-- Support Guidelines -->
                    <x-card class="rounded-lg shadow-sm">
                        <x-slot:header>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                                    <x-heroicon-o-light-bulb class="w-6 h-6 text-primary-600" />
                                </div>

                                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-300">
                                    {{ t('support_guidelines') }}
                                </h2>
                            </div>
                        </x-slot:header>
                        <x-slot:content class="space-y-6">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <div class="w-2 h-2 bg-primary-500 rounded-full mr-3"></div>
                                    {{ t('before_creating_ticket') }}
                                </h4>
                                <ul class="space-y-3">
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <x-heroicon-s-check
                                                class="w-5 h-5 text-emerald-500 dark:text-emerald-400" />
                                        </div>
                                        <span class="ml-3 text-gray-600 dark:text-gray-400 text-sm">
                                            {{ t('check_faq_section_common_solutions') }}</span>
                                    </li>
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <x-heroicon-s-check
                                                class="w-5 h-5 text-emerald-500 dark:text-emerald-400" />
                                        </div>
                                        <span class="ml-3 text-gray-600 dark:text-gray-400 text-sm">
                                            {{ t('search_tickets_avoid_duplicates') }}</span>
                                    </li>
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <x-heroicon-s-check
                                                class="w-5 h-5 text-emerald-500 dark:text-emerald-400" />
                                        </div>
                                        <span class="ml-3 text-gray-600 dark:text-gray-400 text-sm">
                                            {{ t('basic_troubleshooting_steps') }}</span>
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <div class="w-2 h-2 bg-primary-500 rounded-full mr-3"></div>
                                    {{ t('for_faster_resolution') }}
                                </h4>
                                <ul class="space-y-3">
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <x-heroicon-s-exclamation-circle class="w-5 h-5 text-info-500" />

                                        </div>
                                        <span class="ml-3 text-gray-600 dark:text-gray-400 text-sm">
                                            {{ t('specific_detailed_description') }}</span>
                                    </li>
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <x-heroicon-s-exclamation-circle class="w-5 h-5 text-info-500" />
                                        </div>
                                        <span class="ml-3 text-gray-600 dark:text-gray-400 text-sm">{{
                                            t('include_exact_error_messages') }}</span>
                                    </li>
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <x-heroicon-s-exclamation-circle class="w-5 h-5 text-info-500" />
                                        </div>
                                        <span class="ml-3 text-gray-600 dark:text-gray-400 text-sm">
                                            {{ t('attach_relevant_screenshots') }}</span>
                                    </li>
                                    <li class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <x-heroicon-s-exclamation-circle class="w-5 h-5 text-info-500" />
                                        </div>
                                        <span class="ml-3 text-gray-600 dark:text-gray-400 text-sm">
                                            {{ t('choose_appropriate_department') }}</span>
                                    </li>
                                </ul>
                            </div>
                        </x-slot:content>
                    </x-card>
                    <!-- End of Support Guidelines -->
                    <!-- Contact Information -->
                    <x-card class="rounded-lg shadow-sm">
                        <x-slot:header>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                                    <x-heroicon-o-chat-bubble-oval-left-ellipsis class="w-6 h-6 text-primary-600" />
                                </div>

                                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-300">
                                    {{ t('need_immediate_help') }}
                                </h2>
                            </div>
                        </x-slot:header>
                        <x-slot:content class="space-y-2">
                            <div
                                class="flex items-center p-3 bg-info-50 dark:bg-info-900/20 rounded-md border border-info-200 dark:border-info-800">
                                <x-heroicon-o-envelope class="w-5 h-5 text-info-600 dark:text-info-400 mr-2" />
                                @php
                                $admin = \App\Models\User::withoutGlobalScopes()
                                ->where('user_type', 'admin')
                                ->where('is_admin', 1)
                                ->first();
                                @endphp
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ t('email_support') }}
                                    </span>
                                    <span class="font-medium text-gray-900 dark:text-gray-400"> {!! $admin
                                        ? '<a href="mailto:' .
                                            $admin->email .
                                            '" class="hover:underline hover:text-info-600 dark:hover:text-info-400">' .
                                            e($admin->email) .
                                            '</a>'
                                        : 'Not Available' !!}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="flex items-center p-3 bg-info-50 dark:bg-info-900/20 rounded-md border border-info-200 dark:border-info-800">
                                <x-heroicon-o-phone class="w-5 h-5 text-info-600 dark:text-info-400 mr-2" />
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ t('phone_support')
                                        }}</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-400"> {!! $admin ?
                                        e($admin->phone) : 'Not Available' !!}
                                    </span>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>