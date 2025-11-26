@props(['assignees'])

<div class="flex flex-wrap -m-1">
    @foreach($assignees as $assignee)
    <div
        class="m-1 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
        {{ $assignee['name'] }}
    </div>
    @endforeach
</div>