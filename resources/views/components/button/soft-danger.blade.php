@props(['disabled' => false])

<x-button :disabled="$disabled" {{ $attributes->merge(['type' => 'button', 'class' => 'bg-danger-100 text-danger-700
  hover:bg-danger-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-300 dark:bg-slate-700
  dark:border-slate-500 dark:text-danger-400 dark:hover:border-danger-600 dark:hover:bg-danger-600 dark:hover:text-white
  dark:focus:ring-offset-slate-800']) }}>
  {{ $slot }}
</x-button>