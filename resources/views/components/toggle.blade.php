@props(['id' => null, 'name' => null, 'value' => false])

<div x-data="{ isOn: {{ $value ? 'true' : 'false' }} }">
    <label class="relative inline-flex items-center cursor-pointer mt-2 group">
        <input type="checkbox" x-model="isOn" @if ($id) id="{{ $id }}" @endif @if ($name) name="{{ $name }}" @endif
            class="sr-only peer" @change="$dispatch('toggle-changed', { value: isOn })" {{ $attributes }}>
        <div class="w-11 h-6 bg-gray-200 rounded-full peer transition-all duration-300 ease-in-out
            peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 peer-focus:ring-opacity-50
            dark:peer-focus:ring-primary-800 dark:bg-gray-700 dark:border-gray-600
            peer-checked:after:translate-x-full peer-checked:after:border-white 
            after:content-[''] after:absolute after:top-0.5 after:left-[2px] 
            after:bg-white after:border-gray-300 after:border after:rounded-full 
            after:h-5 after:w-5 after:transition-all after:duration-300 after:ease-in-out
            after:shadow-md hover:after:shadow-lg
            peer-checked:bg-primary-600 peer-checked:shadow-lg
            hover:bg-gray-300 dark:hover:bg-gray-600
            peer-checked:hover:bg-primary-700
            group-hover:scale-105 transform transition-transform duration-200">
        </div>
    </label>
</div>