
<div>
    <!-- Start block  -->
    <section class="bg-white dark:bg-gray-900">
        <div
            class="grid justify-center  max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
            <div class="mr-auto place-self-center lg:col-span-7">

                <div class="px-5 flex items-center mb-4 h-[70px]">
                    <h1
                        class="sm:py-[1px] font-sans px-2 flex items-center  font-semibold bg-cyan-50 border-white/70 border-2 text-neutral-600 rounded-2xl ">
                         {{ $themeSettings['theme.hero_heading'] }}</h1>
                </div>

                <h1 id="heroSectionText"
                    class="mb-4 inline lg:text-3xl text-2xl min-h-[65px] font-bold font-display leading-none tracking-tight md:text-5xl xl:text-6xl dark:text-white">
                    {{ $themeSettings['theme.title'] }}
                </h1>
                @if ($themeSettings['theme.description'])
                <p class="max-w-2xl my-4  text-gray-600 lg:mb-8 md:text-lg lg:text-lg dark:text-gray-500">
                    {!! $themeSettings['theme.description'] !!}
                </p>
                @endif

                <div class="space-y-4 sm:space-y-0 ">
                    @if ($themeSettings['theme.primary_button_text'])
                    @if ($themeSettings['theme.primary_button_type'] === 'outline')
                    <x-button.outline href="{{ $themeSettings['theme.primary_button_url'] }}">
                        {{ $themeSettings['theme.primary_button_text'] }}
                        <x-heroicon-o-arrow-up-right class="w-4 h-5 ml-2" />
                    </x-button.outline>
                    @else
                    <a href="{{ $themeSettings['theme.primary_button_url'] }}">
                        <x-button.primary class="sm:w-auto w-full">
                            {{ $themeSettings['theme.primary_button_text'] }}
                            <x-heroicon-o-arrow-up-right class="w-4 h-5 ml-2" />
                        </x-button.primary>
                    </a>
                    @endif
                    @endif

                    @if ($themeSettings['theme.secondary_button_text'])
                    @if ($themeSettings['theme.secondary_button_type'] === 'outline')
                    <x-button.outline href="{{ $themeSettings['theme.secondary_button_url'] }}">
                        {{ $themeSettings['theme.secondary_button_text'] }}</x-button.outline>
                    @else
                    <a href="{{ $themeSettings['theme.secondary_button_url'] }}">
                        <x-button.primary class="sm:w-auto w-full">
                            {{ $themeSettings['theme.secondary_button_text'] }}
                        </x-button.primary>
                    </a>
                    @endif
                    @endif
                </div>
            </div>

            <div class="hidden lg:mt-0 lg:col-span-5 lg:flex w-[590px] justify-center items-center">
                @php
                // Get the image path from settings
                $imagePath = $themeSettings['theme.image_path']
                ? Storage::url($themeSettings['theme.image_path'])
                : asset('img/dummy-image/dummy_450x400.png');
                @endphp
                <img src="{{ $imagePath }}" data-aos="fade-down" data-aos-once="true" data-aos-duration="3000"
                    class="max-w-full h-auto object-contain"
                    alt="{{ $themeSettings['theme.image_alt_text'] ?? 'hero image' }}" />
            </div>

        </div>
    </section>
    <!-- End block -->
    @if (empty($themeSettings['theme.title']) &&
    empty($themeSettings['theme.primary_button_text']) &&
    empty($themeSettings['theme.secondary_button_text']) &&
    empty($themeSettings['theme.image_path']))
    <!-- Fallback when no hero section is active -->
    <section class="bg-white dark:bg-gray-900">
        <div class="max-w-screen-xl px-4 py-8 mx-auto lg:py-16">
            <div class="text-center">
                <p class="text-gray-500 dark:text-gray-400">No hero section available</p>
            </div>
        </div>
    </section>
    @endif
</div>