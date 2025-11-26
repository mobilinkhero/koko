@props([
'type' => 'primary',
'dismissable' => false,
'message' => '',
'title' => '',
])

@php
// Light mode classes for each alert type
$alertClasses = [
'primary' => 'bg-info-100 border-info-500 text-info-700',
'info' => 'bg-teal-100 border-teal-500 text-teal-700',
'warning' => 'bg-warning-100 border-warning-500 text-warning-800',
'danger' => 'bg-danger-100 border-danger-500 text-danger-700',
'success' => 'bg-success-100 border-success-500 text-success-700',
];

// Dark mode classes for each alert type
$darkAlertClasses = [
'primary' => 'dark:bg-gray-700 dark:border-info-600 dark:text-white',
'info' => 'dark:bg-gray-700 dark:border-teal-300 dark:text-teal-300',
'warning' => 'dark:bg-gray-700 dark:border-warning-300 dark:text-warning-300',
'danger' => 'dark:bg-gray-700 dark:border-danger-300 dark:text-danger-300',
'success' => 'dark:bg-gray-700 dark:border-success-300 dark:text-success-300',
];

// Determine the classes to apply based on the alert type
$class = $alertClasses[$type] ?? $alertClasses['primary'];
$darkClass = $darkAlertClasses[$type] ?? $darkAlertClasses['primary'];
@endphp

<div {{ $attributes->merge(['class' => "px-4 py-3 border-l-4 rounded-r-md z-100 flex $class $darkClass"]) }}
  role="alert">
  <div class="flex flex-col">
    @if ($title)
    <strong class="font-semibold text-sm mb-1">{{ $title }}</strong>
    @endif
    <span class="block sm:inline text-sm leading-6">
      {{ $message ?: $slot }}
    </span>
  </div>
  <div class="flex items-center">
    @if ($dismissable)
    <span class=" px-2 py-1 cursor-pointer" onclick="this.parentElement.style.display='none'">
      <x-heroicon-c-x-mark class="w-5 h-5" />
    </span>
    @endif
  </div>
</div>