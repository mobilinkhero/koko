<x-app-layout>
    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('tickets'), 'route' => route('admin.tickets.index')],
        ['label' => t('ticket_details')]
]" />
    <div class="flex flex-col sm:flex-row sm:items-center items-start justify-between gap-2">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ t('ticket') }}{{ $ticket->ticket_id }}
        </h2>
        <div class="flex  justify-start mb-3 px-0 sm:px-5 lg:px-0 items-center gap-2">
            <x-button.secondary href="{{ route('admin.tickets.index') }}">
                <x-heroicon-o-arrow-small-left class="w-4 h-4 mr-1" />{{ t('back_to_tickets') }}
            </x-button.secondary>
            @if ($ticket->status !== 'closed')
            <form action="{{ route('admin.tickets.close', $ticket->id) }}" method="POST">
                @csrf
                <x-button.green type="submit">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>{{ t('close_ticket') }}
                </x-button.green>
            </form>
            @else
            <form action="{{ route('admin.tickets.reopen', $ticket->id) }}" method="POST">
                @csrf
                <x-button.primary type="submit">
                    <x-heroicon-s-arrow-path class="w-5 h-5 mr-1" />{{ t('reopen_ticket') }}
                </x-button.primary>
            </form>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <!-- Enhanced Ticket Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <!-- Tenant Card -->
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all duration-200">
                <div
                    class="bg-gradient-to-r from-info-50 to-info-100 dark:from-info-900/20 dark:to-info-800/20 px-3 py-2 border-b border-info-200/50 dark:border-info-700/50">
                    <div class="flex items-center space-x-2">
                        <div class="bg-info-500 p-1 rounded">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-info-700 dark:text-info-300">{{ t('tenant') }}</h3>
                    </div>
                </div>
                <div class="p-3">
                    @if ($ticket->tenantStaff)
                    <div class="space-y-0.5">
                        <p class="font-medium text-gray-900 dark:text-gray-100 leading-tight text-sm">
                            {{ $ticket->tenantStaff->firstname . ' ' . $ticket->tenantStaff->lastname }}
                            <span class="text-info-600 dark:text-info-400 text-sm">({{ $ticket->tenantStaff->is_admin ?
                                'Admin' : 'Staff' }})</span>
                        </p>
                        <p class=" text-gray-600 dark:text-gray-400 truncate text-sm">
                            {{ $ticket->tenantStaff->email }}
                        </p>
                    </div>
                    @elseif($ticket->tenant && $ticket->tenant->adminUser)
                    <div class="space-y-0.5">
                        <p class="font-medium text-gray-900 dark:text-gray-100 leading-tight text-sm">
                            {{ $ticket->tenant->adminUser->firstname . ' ' . $ticket->tenant->adminUser->lastname }}
                            <span class="text-info-600 dark:text-info-400 text-sm">{{ t('admin_') }}</span>
                        </p>
                        <p class=" text-gray-600 dark:text-gray-400 truncate text-sm">
                            {{ $ticket->tenant->adminUser->email }}
                        </p>
                    </div>
                    @else
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">
                            {{ $ticket->tenant ? $ticket->tenant->company_name : 'N/A' }}
                        </p>
                        <p class=" text-gray-500 dark:text-gray-400 text-sm">{{ 'no_staff_assigned' }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Department Card -->
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all duration-200">
                <div
                    class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 px-3 py-2 border-b border-purple-200/50 dark:border-purple-700/50">
                    <div class="flex items-center space-x-2">
                        <div class="bg-purple-500 p-1 rounded">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-purple-700 dark:text-purple-300">{{ t('department') }}</h3>
                    </div>
                </div>
                <div class="p-3 flex items-center justify-center">
                    <p
                        class="font-medium p-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300 text-center text-sm">
                        {{ $ticket->department?->name ?? 'N/A' }}
                    </p>
                </div>

            </div>

            <!-- Priority Card -->
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all duration-200">
                <div
                    class="bg-gradient-to-r from-warning-50 to-warning-100 dark:from-warning-900/20 dark:to-warning-800/20 px-3 py-2 border-b border-warning-200/50 dark:border-warning-700/50">
                    <div class="flex items-center space-x-2">
                        <div class="bg-warning-500 p-1 rounded">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-warning-700 dark:text-warning-300">{{ t('priority') }}</h3>
                    </div>
                </div>
                <div class="p-3 flex items-center justify-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-sm font-medium {{ match ($ticket->priority) {
                            'high' => 'bg-danger-100 text-danger-700 dark:bg-danger-900/50 dark:text-danger-300',
                            'medium' => 'bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-300',
                            'low' => 'bg-success-100 text-success-700 dark:bg-success-900/50 dark:text-success-300',
                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        } }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </div>
            </div>

            <!-- Status Card -->
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all duration-200">
                <div
                    class="bg-gradient-to-r from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 px-3 py-2 border-b border-emerald-200/50 dark:border-emerald-700/50">
                    <div class="flex items-center space-x-2">
                        <div class="bg-emerald-500 p-1 rounded">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-emerald-700 dark:text-emerald-300">{{ t('status') }}</h3>
                    </div>
                </div>
                <div class="p-3 flex items-center justify-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-sm font-medium {{ match ($ticket->status) {
                            'open' => 'bg-info-100 text-info-700 dark:bg-info-900/50 dark:text-info-300',
                            'pending' => 'bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-300',
                            'answered' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
                            'closed' => 'bg-success-100 text-success-700 dark:bg-success-900/50 dark:text-success-300',
                            'on_hold' => 'bg-gray-100 text-gray-700 dark:bg-gray-700/80 dark:text-gray-300',
                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700/80 dark:text-gray-300',
                        } }}">
                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                    </span>
                </div>
            </div>

            <!-- Created Date Card -->
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all duration-200">
                <div
                    class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 px-3 py-2 border-b border-primary-200/50 dark:border-primary-700/50">
                    <div class="flex items-center space-x-2">
                        <div class="bg-primary-500 p-1 rounded">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-primary-700 dark:text-primary-300">{{ t('created') }}</h3>
                    </div>
                </div>
                <div class="p-3 flex items-center justify-center">
                    <div class="text-center">
                        <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">
                            {{ $ticket->created_at->format('M d, Y') }}
                        </p>
                        <p class=" text-gray-500 dark:text-gray-400 text-sm">
                            {{ $ticket->created_at->format('H:i') }} • {{ $ticket->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Details and Replies -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <livewire:tickets::admin.ticket-details :ticket="$ticket" />
            </div>

            <!-- Enhanced Sidebar -->
            <div class="space-y-6">
                <!-- Department Assignees -->
                <x-card class="rounded-lg shadow-sm"
                    x-data="assigneeManager({{ json_encode($autoAssignedUsers) }}, {{ json_encode($adminUser) }})">
                    <x-slot:header>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                            <div class="bg-primary-100 dark:bg-primary-900/60 p-2 rounded-lg mr-3 shadow-sm">
                                <x-heroicon-o-users class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                            </div>
                            {{ t('assigned_admins') }}
                        </h3>
                    </x-slot:header>
                    <x-slot:content class="space-y-4">
                        <!-- Display assigned users -->
                        <ul class="flex flex-wrap gap-2 mb-3">
                            <template x-for="(user, index) in selected" :key="user.user_id">
                                <li
                                    class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                                    <span x-text="user.name"></span>
                                    <button @click="removeUser(user.user_id)"
                                        class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">
                                        <x-heroicon-m-x-mark class="w-4 h-4" />
                                    </button>
                                </li>
                            </template>
                        </ul>

                        <!-- Custom dropdown as ul/li -->
                        <div class="relative mb-4" x-data="{ showList: false }">
                            <button @click="showList = !showList" type="button"
                                class="w-full border px-3 py-2 rounded bg-white dark:bg-gray-800 text-sm text-gray-800 dark:text-white flex justify-between items-center">
                                <span>{{ t('select_admin_to_add') }}</span>
                                <svg class="h-4 w-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <ul x-show="showList" @click.outside="showList = false"
                                class="absolute z-10 w-full mt-2 bg-white border rounded shadow max-h-60 overflow-auto dark:bg-gray-800">
                                <template x-if="available.length > 0">
                                    <template x-for="user in available" :key="user.id">
                                        <li @click="addUserFromList(user); showList = false"
                                            class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-700 dark:text-white">
                                            <span x-text="user.firstname + ' ' + user.lastname"></span>
                                            <span class="text-xs text-gray-500 ml-2" x-text="user.email"></span>
                                        </li>
                                    </template>
                                </template>
                                <template x-if="available.length === 0">
                                    <li class="px-4 py-2 text-gray-400 dark:text-gray-500 text-sm">
                                        {{ t('no_users_available_assign') }}
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </x-slot:content>
                    <!-- Submit to update -->
                    <x-slot:footer>
                        <div class="flex justify-end">
                            <form :action="`{{ route('admin.tickets.update-assignees', $ticket->id) }}`" method="POST">
                                @csrf
                                @method('PUT')

                                <template x-for="user in selected">
                                    <input type="hidden" name="assignee_id[]" :value="user.user_id">
                                </template>

                                <x-button.primary type="submit">
                                    {{ t('update_assigned') }}
                                </x-button.primary>
                            </form>
                        </div>
                    </x-slot:footer>
                </x-card>

                <!-- Quick Actions -->
                <x-card class="rounded-lg shadow-sm">
                    <x-slot:header>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                            <div class="bg-primary-100 dark:bg-primary-900/60 p-2 rounded-lg mr-3 shadow-sm">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            {{ t('quick_actions') }}
                        </h3>
                    </x-slot:header>
                    <x-slot:content class="space-y-4">
                        <div class="group">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors duration-200"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ t('change_status') }}
                            </label>
                            <select wire:model="status" wire:change="changeStatus"
                                class="tom-select block w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600"
                                id="statusSelect" {{ $ticket->status === 'closed' ? 'disabled' : '' }}>
                                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>
                                    {{ t('open') }}
                                </option>
                                <option value="answered" {{ $ticket->status === 'answered' ? 'selected' : '' }}>
                                    {{ t('answered') }}</option>
                                <option value="on_hold" {{ $ticket->status === 'on_hold' ? 'selected' : '' }}>
                                    {{ t('on_hold') }}
                                </option>
                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>
                                    {{ t('closed') }}
                                </option>
                            </select>
                        </div>

                        <!-- Priority Change -->
                        <div class="group">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors duration-200"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                {{ t('change_priority') }}
                            </label>
                            <select wire:model="priority" wire:change="changePriority"
                                class="tom-select block w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600"
                                id="prioritySelect" {{ $ticket->status === 'closed' ? 'disabled' : '' }}>
                                <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>
                                    {{ t('low') }}
                                </option>
                                <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>
                                    {{ t('medium') }}
                                </option>
                                <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>
                                    {{ t('high') }}
                                </option>
                            </select>
                        </div>

                        <!-- Department Assignment -->
                        <div class="group">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors duration-200"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                {{ t('change_department') }}
                            </label>
                            <select wire:model="department_id" wire:change="changeDepartment"
                                class="tom-select block w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600"
                                id="departmentSelect" {{ $ticket->status === 'closed' ? 'disabled' : '' }}>
                                @foreach ($departments as $department)
                                <option value="{{ $department->id }}" {{ $ticket->department_id === $department->id
                                    ?
                                    'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                    </x-slot:content>
                </x-card>

                <!-- Ticket Information -->
                <x-card class="rounded-lg shadow-sm">
                    <x-slot:header>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                            <div class="bg-primary-100 dark:bg-primary-900/60 p-2 rounded-lg mr-3 shadow-sm">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            {{ t('ticket_information') }}
                        </h3>
                    </x-slot:header>

                    <x-slot:content>
                        <dl class="space-y-3">
                            <div
                                class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                    </svg>
                                    {{ t('ticket_id') }}
                                </dt>
                                <dd
                                    class="text-sm font-mono text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-md">
                                    {{ $ticket->ticket_id }}</dd>
                            </div>

                            <div
                                class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    {{ t('created_1') }}
                                </dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ format_date_time($ticket->created_at) }}</dd>
                            </div>

                            <div
                                class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ t('last_updated') }}
                                </dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ format_date_time($ticket->updated_at) }}</dd>
                            </div>

                            <div
                                class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                    {{ t('replies') }}
                                </dt>
                                <dd
                                    class="text-sm text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-md">
                                    {{ $ticket->replies->count() }}</dd>
                            </div>

                            <div
                                class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ t('admin_viewed') }}
                                </dt>
                                <dd>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->admin_viewed ? 'bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300 border border-success-200 dark:border-success-800' : 'bg-warning-100 text-warning-800 dark:bg-warning-900/50 dark:text-warning-300 border border-warning-200 dark:border-warning-800' }}">
                                        {{ $ticket->admin_viewed ? 'Yes' : 'No' }}
                                    </span>
                                </dd>
                            </div>

                            <div
                                class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ t('tenant_viewed') }}
                                </dt>
                                <dd>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->tenant_viewed ? 'bg-success-100 text-success-800 dark:bg-success-900/50 dark:text-success-300 border border-success-200 dark:border-success-800' : 'bg-warning-100 text-warning-800 dark:bg-warning-900/50 dark:text-warning-300 border border-warning-200 dark:border-warning-800' }}">
                                        {{ $ticket->tenant_viewed ? 'Yes' : 'No' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>

                        @if ($ticket->attachments && count($ticket->attachments) > 0)
                        <div class="mt-6 pt-5 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-primary-500 dark:text-primary-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                {{ t('original_attachments') }}
                            </h4>
                            <ul class="space-y-2">
                                @foreach ($ticket->attachments as $attachment)
                                <li>
                                    @if (is_array($attachment))
                                    {{-- New format: array with filename, path, size --}}
                                    <a href="{{ route('admin.tickets.download-attachment', [
                                                    'ticket' => $ticket->id,
                                                    'file' => $attachment['filename'],
                                                ]) }}"
                                        class="text-primary-600 hover:text-primary-500 dark:text-primary-400 text-sm">
                                        {{ $attachment['filename'] }}
                                    </a>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        ({{ number_format($attachment['size'] / 1024, 1) }} KB)
                                    </span>
                                    @else
                                    {{-- Legacy format: just filename string --}}
                                    <a href="{{ route('admin.tickets.download-attachment', [
                                                    'ticket' => $ticket->id,
                                                    'file' => $attachment,
                                                ]) }}"
                                        class="text-primary-600 hover:text-primary-500 dark:text-primary-400 text-sm">
                                        {{ $attachment }}
                                    </a>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ t('file') }}</span>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </x-slot:content>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var statusSelect = document.getElementById("statusSelect");
        if (statusSelect) {
            statusSelect.addEventListener('change', function () {
                var ticketId = {{ $ticket->id }};
                var selectedStatus = statusSelect.value;

                fetch(`/admin/tickets/${ticketId}/status`, {
                    method: 'POST',
                    body: JSON.stringify({
                        status: selectedStatus,
                        ticketId: ticketId
                    }),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('{{ t('error_updating_ticket_status') }}');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ t('error_updating_the_ticket_status') }}');
                });
            });
        }

        var prioritySelect = document.getElementById("prioritySelect");
        if (prioritySelect) {
            prioritySelect.addEventListener('change', function () {
                var ticketId = {{ $ticket->id }};
                var selectedPriority = prioritySelect.value;

                fetch(`/admin/tickets/${ticketId}/priority`, {
                    method: 'POST',
                    body: JSON.stringify({
                        priority: selectedPriority,
                        ticketId: ticketId
                    }),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('{{ t('error_updating_the_ticket_priority') }}');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ t('error_updating_the_ticket_priority') }}');
                });
            });
        }

        var departmentSelect = document.getElementById("departmentSelect");
        if (departmentSelect) {
            departmentSelect.addEventListener('change', function () {
                var ticketId = {{ $ticket->id }};
                var selectedDepartment = departmentSelect.value;

                fetch(`/admin/tickets/${ticketId}/department`, {
                    method: 'POST',
                    body: JSON.stringify({
                        department_id: selectedDepartment,
                        ticketId: ticketId
                    }),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('{{ t('error_assigning_ticket_department') }}');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ t('error_assigning_ticket_to_department') }}');
                });
            });
        }
    });
</script>
<script>
    function assigneeManager(initialSelected, availableUsers) {
    return {
        selected: initialSelected,
        available: availableUsers,

        addUserFromList(user) {
            this.selected.push({
                user_id: user.id,
                name: user.firstname + ' ' + user.lastname,
                email: user.email
            });

            this.available = this.available.filter(u => u.id != user.id);
        },

        removeUser(userId) {
            const user = this.selected.find(u => u.user_id == userId);
            if (user) {
                this.selected = this.selected.filter(u => u.user_id != userId);

                this.available.push({
                    id: user.user_id,
                    firstname: user.name.split(' ')[0],
                    lastname: user.name.split(' ')[1] ?? '',
                    email: user.email
                });
            }
        }
    }
}

</script>