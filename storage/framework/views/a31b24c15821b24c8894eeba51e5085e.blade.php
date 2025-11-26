<?php extract((new \Illuminate\Support\Collection($attributes->getAttributes()))->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>

<x-heroicon-o-square-3-stack-3d  {{ $attributes }}>

{{ $slot ?? "" }}
</x-heroicon-o-square-3-stack-3d>