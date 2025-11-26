@props(['maxWidth', 'title' => '', 'description' => ''])

@php
$maxWidth = [
'sm' => 'sm:max-w-sm',
'md' => 'sm:max-w-md',
'lg' => 'sm:max-w-lg',
'xl' => 'sm:max-w-xl',
'2xl' => 'sm:max-w-2xl',
'3xl' => 'sm:max-w-3xl',
'4xl' => 'sm:max-w-4xl',
][$maxWidth ?? '2xl'];
@endphp

<div x-data="{ show: @entangle($attributes->wire('model')) }" x-on:close.stop="show = false"
  x-on:keydown.escape.window="show = false" x-show="show"
  class="jetstream-modal fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50 flex items-center justify-center backdrop-blur-sm bg-gradient-to-br from-black/30 to-black/60"
  style="display: none;">
  <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="show = false"
    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
  </div>

  <div x-show="show"
    class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
    x-trap.inert.noscroll="show" x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

    <div class="w-full flex p-5 flex-col sm:flex-row">
      <div
        class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-danger-100 sm:mx-0 sm:size-10">
        <x-heroicon-o-exclamation-triangle class="w-7 h-7 text-danger-600 " />
      </div>
      <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
        <h3 class="text-base font-semibold text-slate-700 dark:text-slate-200" id="modal-title">
          {{ $title }}</h3>
        <div class="mt-2">
          <p class="text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
        </div>
      </div>
    </div>
    <div class="bg-gray-100 dark:bg-gray-700  px-4 py-2 sm:flex sm:flex-row-reverse items-center">
      {{ $slot }}
    </div>
  </div>
</div>