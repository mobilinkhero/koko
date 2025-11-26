@props(['disabled' => false])

<x-button :disabled="$disabled" {{ $attributes->merge(['type' => 'button', 'class' => 'text-info-600 hover:text-info-500
  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-500 dark:text-info-400 dark:hover:text-info-300
  dark:focus:ring-offset-slate-800']) }}>
  {{ $slot }}
</x-button>