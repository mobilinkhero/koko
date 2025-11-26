@props(['color' => 'secondary', 'text' => '', 'size' => 'sm'])

@php
  $colors = [
      'primary' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/20 dark:text-primary-400',
      'secondary' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-700 dark:text-secondary-300',
      'success' => 'bg-success-100 text-success-800 dark:bg-success-900/20 dark:text-success-400',
      'danger' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/20 dark:text-danger-400',
      'warning' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/20 dark:text-warning-400',
      'info' => 'bg-info-100 text-info-800 dark:bg-info-900/20 dark:text-info-400',
  ];

  $sizes = [
      'xs' => 'px-2 py-1 text-xs',
      'sm' => 'px-2.5 py-1.5 text-xs',
      'md' => 'px-3 py-2 text-sm',
      'lg' => 'px-4 py-2 text-base',
  ];

  $colorClass = $colors[$color] ?? $colors['secondary'];
  $sizeClass = $sizes[$size] ?? $sizes['sm'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 font-medium rounded-full $colorClass $sizeClass"]) }}>
  @if($text)
    {{ $text }}
  @else
    {{ $slot }}
  @endif
</span>
