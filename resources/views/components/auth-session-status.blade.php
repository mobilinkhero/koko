@props([
'status' => session('status'),
'error' => session('error'),
])

@if ($status)
<div {{ $attributes->merge(['class' => 'mb-4 text-sm bg-success-50 border-l-4 border-success-300 text-success-800 px-2
  py-3 rounded dark:bg-gray-800 dark:border-success-800 dark:text-success-300']) }}
  role="alert">
  {{ $status }}
</div>
@endif

@if ($error)
<div {{ $attributes->merge(['class' => 'mb-4 text-sm bg-danger-50 border-l-4 border-danger-300 text-danger-800 px-2 py-3
  rounded
  dark:bg-gray-800 dark:border-danger-800 dark:text-danger-300']) }}
  role="alert">
  {{ $error }}
</div>
@endif