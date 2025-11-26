<?php extract((new \Illuminate\Support\Collection($attributes->getAttributes()))->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
@props(['width'])
<x-livewire-powergrid::icons.chevron-down :width="$width" >

{{ $slot ?? "" }}
</x-livewire-powergrid::icons.chevron-down>