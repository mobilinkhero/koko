<x-app-layout>
    <div>
        <x-breadcrumb :items="[
            ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
            ['label' => t('tickets'), 'route' => route('admin.tickets.index')],
            ['label' => t('create_new_ticket')],
        ]" />
    </div>

    <div class="py-6">
        <div class="mx-auto">
            <!-- Create Ticket Form -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div
                        class="bg-white ring-1 ring-slate-300 rounded-lg dark:bg-transparent dark:ring-slate-600  shadow-sm w-ful">

                        <div class="border-b border-slate-300 px-4 py-5 sm:px-6 dark:border-slate-600">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                    <x-heroicon-o-document-text class="w-6 h-6 text-primary-600 " />
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                                        {{ t('ticket_details') }}
                                    </h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        {{ t('fill_in_the_information_below') }}</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            @if(isset($ticket))
                            <livewire:tickets::admin.ticket-form :ticket="$ticket" />
                            @else
                            <livewire:tickets::admin.ticket-form />
                            @endif
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Guidelines Card -->
                    <x-card class="rounded-lg shadow-sm">
                        <x-slot:header>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                                    <x-heroicon-o-exclamation-circle class="w-6 h-6 text-primary-600 " />
                                </div>

                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                                    {{ t('guidelines') }}
                                </h2>
                            </div>
                        </x-slot:header>
                        <x-slot:content>
                            <div class="mb-5 pb-5 ">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-primary-500 dark:text-primary-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ t('creating_a_ticket') }}
                                </h4>
                                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2 ml-2 ">
                                    <li class="flex items-start">
                                        <span
                                            class="inline-block w-1.5 h-1.5 bg-primary-500 dark:bg-primary-400 rounded-full mt-1.5 mr-2"></span>
                                        <span>{{ t('select_the_appropriate_client_for_this_ticket') }}</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span
                                            class="inline-block w-1.5 h-1.5 bg-primary-500 dark:bg-primary-400 rounded-full mt-1.5 mr-2"></span>
                                        <span>{{ t('choose_the_correct_department_based_on_the_issue_type') }}</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span
                                            class="inline-block w-1.5 h-1.5 bg-primary-500 dark:bg-primary-400 rounded-full mt-1.5 mr-2"></span>
                                        <span>{{ t('set_priority_based_on_urgency_and_impact') }}</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span
                                            class="inline-block w-1.5 h-1.5 bg-primary-500 dark:bg-primary-400 rounded-full mt-1.5 mr-2"></span>
                                        <span>{{ t('provide_a_clear_and_descriptive_subject') }}</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span
                                            class="inline-block w-1.5 h-1.5 bg-primary-500 dark:bg-primary-400 rounded-full mt-1.5 mr-2"></span>
                                        <span>{{ t('include_all_relevant_details_in_the_description') }}</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span
                                            class="inline-block w-1.5 h-1.5 bg-primary-500 dark:bg-primary-400 rounded-full mt-1.5 mr-2"></span>
                                        <span>{{ t('attach_any_supporting_files_if_necessary') }}</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="mb-5 pb-5 ">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-primary-500 dark:text-primary-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    {{ t('priority_levels') }}
                                </h4>
                                <ul class="text-sm space-y-3">
                                    <li class="flex items-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900/50 dark:text-danger-300 mr-2 border border-danger-200 dark:border-danger-800">{{
                                            t('high') }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">{{
                                            t('critical_issues_requiring_immediate_attention') }}</span>
                                    </li>
                                    <li class="flex items-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/50 dark:text-warning-300 mr-2 border border-warning-200 dark:border-warning-800">
                                            {{ t('medium') }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">{{
                                            t('important_issues_that_should_be_addressed_soon') }}</span>
                                    </li>
                                    <li class="flex items-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300 mr-2 border border-success-200 dark:border-success-800">{{
                                            t('low') }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">{{
                                            t('general_inquiries_or_minor_issues') }}</span>
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-primary-500 dark:text-primary-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                        </path>
                                    </svg>
                                    {{ t('status_options') }}
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                    <div class="flex items-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info-100 text-info-800 dark:bg-info-900/50 dark:text-info-300 mr-2 border border-info-200 dark:border-info-800">{{
                                            t('open') }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">{{ t('new_ticket') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-300 mr-2 border border-primary-200 dark:border-primary-800">
                                            {{ t('answered') }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">{{ t('response_sent') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 mr-2 border border-gray-200 dark:border-gray-600">{{
                                            t('on_hold') }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">{{ t('paused') }}</span>
                                    </div>
                                    <div class="flex items-center sm:col-span-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300 mr-2 border border-success-200 dark:border-success-800">{{
                                            t('closed') }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">{{ t('issue_resolved') }}</span>
                                    </div>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-card>

                    <!-- File Upload Info Card -->
                    <x-card class="rounded-lg shadow-sm">
                        <x-slot:header>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                                    <x-heroicon-o-paper-clip class="w-6 h-6 text-primary-600 " />
                                </div>

                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-300">
                                    {{ t('file_upload') }}
                                </h2>
                            </div>
                        </x-slot:header>
                        <x-slot:content>
                            <div class="flex items-start mb-4">
                                <svg class="w-10 h-10 text-primary-500 dark:text-primary-400 mr-3 flex-shrink-0"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                    </path>
                                </svg>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ t('you_can_attach_files') }}
                                </p>
                            </div>
                            <ul
                                class="text-sm text-gray-600 dark:text-gray-400 space-y-2 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-100 dark:border-gray-700">
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-primary-500 dark:text-primary-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ t('maximum_file_size') }} 10MB {{ t('per_file') }}
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-primary-500 dark:text-primary-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ t('supported_formats') }}
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-primary-500 dark:text-primary-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ t('multiple_files_can_be_attached') }}
                                </li>
                            </ul>
                        </x-slot:content>
                    </x-card>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Subtle fade-in animation for cards
            const cards = document.querySelectorAll('.shadow-md');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 + (index * 100));
            });

            // Listen for ticket creation events
            window.addEventListener('ticket-created', function(event) {
                if (event.detail.ticketId) {

                    // Show a success message before redirecting
                    const successMsg = document.createElement('div');
                    successMsg.className =
                        'fixed top-4 right-4 bg-success-100 border-l-4 border-success-500 text-success-700 p-4 rounded shadow-md z-50 animate-fade-in-right';
                    successMsg.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <p>Ticket created successfully! Redirecting...</p>
                    </div>
                `;
                    document.body.appendChild(successMsg);

                    // Redirect after a short delay
                    setTimeout(() => {
                        window.location.href = '/admin/tickets/' + event.detail.ticketId;
                    }, 1000);
                }
            });
        });
    </script>
    <style>
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-in-right {
            animation: fadeInRight 0.3s ease-out forwards;
        }
    </style>

</x-app-layout>