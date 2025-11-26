@props(['class' => ''])

<button
  {{ $attributes->merge(['type' => 'button', 'class' => 'mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 ring-1 shadow-xs ring-gray-300 dark:ring-gray-600 ring-inset hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto ' . $class]) }}>
  {{ $slot }}
</button>
