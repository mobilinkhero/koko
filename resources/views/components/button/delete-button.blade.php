@props(['class' => ''])
<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex w-full justify-center rounded-md
  bg-danger-600 dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-white dark:text-danger-400 ring-1 ring-danger-600
  dark:ring-gray-600 ring-inset shadow-xs hover:bg-danger-500 dark:hover:bg-danger-500 dark:hover:text-gray-100 sm:ml-3
  sm:w-auto ' . $class]) }}>
  {{ $slot }}
</button>