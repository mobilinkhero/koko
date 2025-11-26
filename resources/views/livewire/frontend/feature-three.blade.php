<div>
    <div class="items-center gap-8 lg:grid lg:grid-cols-2 xl:gap-16">
        {{-- change --}}
        <div style="background-image: url('{{ asset('img/landingpage-image/feature-1/featuresec-bg.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
            class="relative mb-4 rounded-lg lg:mb-0 h-[500px] lg:flex hidden items-center justify-center ">
            @if ($featureSettings['theme.feature_image_three'])
            @php
            // Get the image path from settings
            $imagePath = $featureSettings['theme.feature_image_three']
            ? Storage::url($featureSettings['theme.feature_image_three'])
            : asset('img/landingpage-image/feature-2/feature2-laptop.png');

            @endphp
            <img data-aos-duration="10000" data-aos-once="true" data-aos="fade-down" src="{{ $imagePath}}"
                class="object-contain animate-bounce-slow-subtle" alt="">
            @else
            <img data-aos-duration="10000" data-aos-once="true" data-aos="fade-down"
                src="{{asset('img/dummy-image/dummy_400x300.png')}}" class="object-contain animate-bounce-slow-subtle"
                alt="">
            {{-- left card --}}

            @endif
        </div>
        {{-- change over --}}
        <div class="text-gray-500 lg:p-1 p-4  sm:text-lg dark:text-gray-400">
            <h2 id="featureSectionText"
                class="mb-4 h-[50px]  font-display  inline lg:text-3xl text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                {{ $featureSettings['theme.feature_title_three'] }}</h2>
            <p class="mb-8 f lg:text-lg">{{ $featureSettings['theme.feature_subtitle_three'] }}</p>
            <!-- List -->
            <ul role="list" class="pt-8 space-y-5 border-t border-gray-200 my-7 dark:border-gray-700">
                @php
                $lists = $featureSettings['theme.feature_list_three'] ?? [];
                @endphp
                @foreach ($lists as $list)
                <li class="flex space-x-3">
                    <!-- Icon -->
                    <svg class="flex-shrink-0 w-5 h-5 text-primary-600 dark:text-info-400" fill="currentColor"
                        viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-base font-medium leading-tight text-gray-900 dark:text-white">{{ $list }}</span>
                </li>
                @endforeach
            </ul>
            <p class="lg:text-lg">{{ $featureSettings['theme.feature_description_three'] }}</p>
        </div>

    </div>
</div>