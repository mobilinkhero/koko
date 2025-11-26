<!-- Email Templates List View (templates.blade.php) -->
<div>

    <x-slot:title>
        {{ t('email_templates') }}
    </x-slot:title>

       <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => tenant_route('tenant.dashboard')],
        ['label' => t('email_templates')],
    ]" />

    
<x-card class="rounded-lg">
     <x-slot:content>
    <!-- Templates grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($templates as $template)
        <div class="bg-white dark:bg-slate-800 rounded-lg ring-1 ring-slate-300 dark:ring-slate-600 overflow-hidden hover:shadow-md transition-shadow duration-300">
            <!-- Header with name and status -->
            <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800 truncate">{{ $template->name }}</h3>
                <span
                    class="px-2 py-1 text-xs rounded-full {{ $template->is_active ? 'bg-success-100 text-success-800' : 'bg-danger-100 text-danger-800' }}">
                    {{ $template->is_active ? t('active') : t('inactive') }}
                </span>
            </div>

            <!-- Template details -->
            <div class="p-4">
                <div class="grid grid-cols-2 gap-4 mb-2">
                    <div>
                        <p class="text-xs text-gray-500">{{ t('category') }}</p>
                        <p class="text-sm font-medium text-gray-700">{{ $template->category ?: 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">{{ t('type') }}</p>
                        <p class="text-sm font-medium text-gray-700">{{ $template->type ?: 'N/A' }}</p>
                    </div>
                </div>

                <div class="mb-2">
                    <p class="text-xs text-gray-500">{{ t('subject') }}</p>
                    <p class="text-sm font-medium text-gray-700 truncate">{{ $template->subject }}</p>
                </div>

                @if($template->description)
                <div class="mb-2">
                    <p class="text-xs text-gray-500">{{ t('description') }}</p>
                    <p class="text-sm text-gray-700 line-clamp-2">{{ $template->description }}</p>
                </div>
                @endif

                <div class="flex items-center space-x-2 mt-3">
                    <span
                        class="text-xs px-2 py-1 rounded-full {{ $template->is_system ? 'bg-info-100 text-info-800' : 'bg-purple-100 text-purple-800' }}">
                        {{ $template->is_system ? t('system') : t('custom') }}
                    </span>
                    @if($template->use_layout)
                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-800">
                        {{ t('with_layout') }}
                    </span>
                    @endif
                </div>
            </div>

            <!-- Action button -->
            <div class="border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                @if(checkPermission('tenant.email_template.edit'))
                <a href="{{ tenant_route('tenant.emails.save', ["id"=> $template->id]) }}" class="block w-full
                    text-center py-3 text-sm font-medium text-primary-600 hover:bg-gray-50 transition-colors">
                    {{ t('view_template') }}
                </a>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-500">{{ t('no_templates_found') }}</p>
        </div>
        @endforelse
    </div>
</x-slot:content>
</x-card>
</div>