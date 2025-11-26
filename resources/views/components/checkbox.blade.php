@props(['disabled' => false, 'type' => 'checkbox'])

<input type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
'class' => 'focus:ring-info-500 h-4 w-4 text-info-600 border-slate-300 rounded',
]) !!}>