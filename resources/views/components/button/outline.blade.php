@props(['disabled' => false, 'href' => '#'])

<a href="{{ $href }}" disabled="{{ $disabled }}" {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex
  items-center justify-center w-full px-5 py-2 mb-2 mr-2 text-sm font-medium text-gray-900 bg-white border
  border-gray-300 rounded-lg sm:w-auto focus:outline-none hover:bg-gray-100 hover:text-info-700 focus:z-10 focus:ring-2
  focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600
  dark:hover:text-white dark:hover:bg-gray-700']) }}>
  {{ $slot }}
</a>