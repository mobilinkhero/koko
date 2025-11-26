<div>
    <div class="items-center  gap-8 lg:grid lg:grid-cols-2 xl:gap-16">
        <div class="text-gray-500 lg:p-1 p-4 sm:text-lg dark:text-gray-400">
            <h2 id="uniqueFeatureText"
                class="lg:text-3xl text-2xl inline h-[30px] font-extrabold font-display tracking-tight text-gray-900 dark:text-white">
                {{ $uniqueSettings['theme.uni_feature_title'] ?: 'Default Title' }}
            </h2>
            <p class="mb-8 lg:text-lg">
                {{ $uniqueSettings['theme.uni_feature_sub_title'] ?: 'Default Subtitle' }}
            </p>
            <!-- List -->
            <ul role="list" class="pt-8 space-y-5 border-t border-gray-200 my-7 dark:border-gray-700">
                @php
                $lists = json_decode($uniqueSettings['theme.uni_feature_list'], true) ?? [];
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
            <p class="mb-8 lg:text-lg">{{ $uniqueSettings['theme.uni_feature_description'] }}</p>
        </div>
        <div style="background-image: url('{{ asset('img/landingpage-image/feature-1/featuresec-bg.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
            class="relative  min-h-full items-center  hidden lg:flex justify-center">
            @if ($uniqueSettings['theme.uni_feature_image'])
            @php
            // Get the image path from settings
            $imagePath = $uniqueSettings['theme.uni_feature_image']
            ? Storage::url($uniqueSettings['theme.uni_feature_image'])
            : asset('img/landingpage-image/feature-2/feature2-laptop.png');

            @endphp
            <img data-aos-duration="10000" data-aos-once="true" data-aos="fade-down" src="{{ $imagePath}}"
                class="w-full h-full object-contain animate-bounce-slow-subtle" alt="">
            @else
            <div class="w-lg h-[300px] ">
                <img data-aos="fade-right" data-aos-once="true" data-aos-duration="1000"
                    src="{{asset('img/dummy-image/dummy_400x300.png')}}" class="w-full animate-bounce-slow-subtle"
                    alt="">
            </div>

            @endif
        </div>
    </div>
</div>