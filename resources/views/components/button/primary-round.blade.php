@props(['disabled' => false])

<button {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex
  items-center justify-center p-2 text-sm border border-transparent font-medium disabled:opacity-50
  disabled:pointer-events-none transition text-white rounded-full bg-primary-600 hover:bg-primary-500 focus:outline-none
  focus:ring-2 focus:ring-offset-2 focus:ring-info-500']) }}>
  {{ $slot }}
</button>