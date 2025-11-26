@props(['disabled' => false])

<x-button :disabled="$disabled" {{ $attributes->merge(['type' => 'submit', 'class' => 'text-white bg-success-600
  hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500
  dark:hover:bg-success-500 dark:focus:ring-offset-slate-800']) }}>
  {{ $slot }}
</x-button>