<nav x-data="{ activeSection: window.location.href }" {{ $attributes }}>
    <div class="items-center justify-between w-full lg:flex lg:w-auto lg:order-1" id="mobile-menu-3">
        <ul class="flex flex-col font-medium lg:flex-row lg:space-x-8 lg:mt-0">
            @foreach ($menuItems as $item)
            <li class="relative" x-data="{ open: false }">
                @if ($item->children->count() > 0)
                <!-- Parent menu with dropdown -->
                <button @click="open = !open" @click.away="open = false"
                    class="flex items-center px-3 rounded lg:bg-transparent lg:p-0 dark:text-gray-400 dark:hover:text-white lg:dark:hover:bg-transparent dark:border-gray-700"
                    :class="{
                                    'bg-white text-primary-600': activeSection.startsWith(
                                        '{{ url('/pages/' . $item->slug) }}'),
                                    'text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700': !activeSection.startsWith(
                                        '{{ url('/pages/' . $item->slug) }}')
                                }">
                    {{ $item->title }}
                    <svg class="w-2.5 h-2.5 ml-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 4 4 4-4" />
                    </svg>
                </button>
                <!-- Dropdown menu -->
                <div class="absolute left-0 pt-2 z-50" x-show="open" x-transition @click.away="open = false" x-cloak>
                    <ul
                        class="w-48 py-2 bg-white border border-gray-100 rounded-lg shadow-md dark:bg-gray-700 dark:border-gray-600">
                        @foreach ($item->children as $child)
                        <li>
                            <a href="{{ url('/pages/' . $child->getFullPathWithParents()) }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 dark:text-gray-400 dark:hover:text-white"
                                :class="{
                                                    'bg-white text-primary-600': activeSection.startsWith(
                                                        '{{ url('/pages/' . $child->getFullPathWithParents()) }}'),
                                                    'hover:bg-gray-50 dark:hover:bg-gray-700': !activeSection
                                                        .startsWith(
                                                            '{{ url('/pages/' . $child->getFullPathWithParents()) }}')
                                                }">
                                {{ $child->title }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @else
                <!-- Single menu item without dropdown -->
                <a href="{{ url('/pages/' . $item->slug) }}"
                    class="block px-3 lg:hover:text-primary-600 dark:text-gray-400 dark:hover:text-white lg:dark:hover:bg-transparent dark:border-gray-700"
                    :class="{
                                    'bg-white text-primary-600': activeSection === '{{ url('/pages/' . $item->slug) }}',
                                    'hover:bg-gray-50 dark:hover:bg-gray-700': activeSection !== '{{ url('/pages/' . $item->slug) }}'
                                }">
                    {{ $item->title }}
                </a>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
    {{ $slot }}
</nav>