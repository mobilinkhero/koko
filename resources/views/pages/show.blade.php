@extends('layouts.landingpage')

@section('content')


<div class="max-w-7xl mx-auto px-4 py-16">
    <x-card class="rounded-lg">
        <x-slot:header>
            <h1 class="text-3xl font-bold mb-4 break-words">{{ $page->title }}</h1>
        </x-slot:header>
        <x-slot:content>

            <div class="prose max-w-none break-words">
                {!! $page->description !!}
            </div>
        </x-slot:content>
    </x-card>
</div>
@endsection