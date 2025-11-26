<div>
    <section class="bg-white dark:bg-gray-900 py-12 relative overflow-hidden">
        <!-- Background decoration -->
        <div
            class="absolute inset-0 bg-gradient-to-r from-gray-50 via-white to-gray-50 dark:from-gray-800 dark:via-gray-900 dark:to-gray-800">
        </div>

        <div class="relative max-w-screen-xl mx-auto px-4">
            <!-- Section Header -->
            <div class="text-center mb-12" data-aos="fade-up" data-aos-duration="800">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 font-display">
                    Trusted by Leading Companies
                </h2>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Join thousands of satisfied clients who trust our platform for their business growth
                </p>
            </div>

            <!-- Infinite Scroll Container -->
            <div class="overflow-hidden whitespace-nowrap relative" data-aos="fade-up" data-aos-duration="1000"
                data-aos-delay="200">

                <!-- Gradient overlays for smooth fade effect -->
                <div
                    class="absolute left-0 top-0 w-20 h-full bg-gradient-to-r from-white via-white/80 to-transparent dark:from-gray-900 dark:via-gray-900/80 dark:to-transparent z-10 pointer-events-none">
                </div>
                <div
                    class="absolute right-0 top-0 w-20 h-full bg-gradient-to-l from-white via-white/80 to-transparent dark:from-gray-900 dark:via-gray-900/80 dark:to-transparent z-10 pointer-events-none">
                </div>

                <!-- Scrolling content -->
                <!-- Scrolling content -->
                <div class="flex animate-scroll-logos">
                    @php
                    // Get logos from settings
                    $settings = get_batch_settings(['theme.partner_logos']);
                    $logosJson = $settings['theme.partner_logos'];
                    $logos = $logosJson ? json_decode($logosJson, true) : [];

                    // If no logos configured, use fallback placeholders
                    if (!is_array($logos) || count($logos) === 0) {
                    $logos = array_fill(0, 8, [
                    'alt' => 'Partner logo placeholder',
                    ]);
                    }

                    // Duplicate logos multiple times to ensure smooth scrolling regardless of count
                    $duplicatedLogos = [];
                    $minLogosNeeded = 12; // Minimum logos needed for smooth infinite scroll

                    if (count($logos) < $minLogosNeeded) { // Repeat the logos array until we have enough
                        $repetitions=ceil($minLogosNeeded / count($logos)); for ($i=0; $i < $repetitions; $i++) {
                        $duplicatedLogos=array_merge($duplicatedLogos, $logos); } } else { $duplicatedLogos=$logos; }
                        @endphp <!-- First set of logos -->
                        <div class="flex items-center space-x-12 lg:space-x-16 px-8 animate-scroll">
                            @foreach ($duplicatedLogos as $index => $logo)
                            <div class="flex-shrink-0 logo-container group">
                                @if (isset($logo['url']) && !empty($logo['url']))
                                <a href="{{ $logo['url'] }}" target="_blank" rel="noopener" class="block">
                                    <img src="{{ isset($logo['path']) ? asset('storage/' . $logo['path']) : asset('img/dummy-image/dummy_192x48.png') }}"
                                        alt="{{ $logo['alt'] ?? 'Partner logo' }}"
                                        class="logo-image h-8 sm:h-10 lg:h-12 w-auto max-w-32 sm:max-w-40 lg:max-w-48 object-contain opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all duration-300"
                                        loading="lazy" />
                                </a>
                                @else
                                <img src="{{ isset($logo['path']) ? asset('storage/' . $logo['path']) : asset('img/dummy-image/dummy_192x48.png') }}"
                                    alt="{{ $logo['alt'] ?? 'Partner logo' }}"
                                    class="logo-image h-8 sm:h-10 lg:h-12 w-auto max-w-32 sm:max-w-40 lg:max-w-48 object-contain opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all duration-300"
                                    loading="lazy" />
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <!-- Duplicate set for seamless loop -->
                        <div class="flex items-center space-x-12 lg:space-x-16 px-8">
                            @foreach ($duplicatedLogos as $index => $logo)
                            <div class="flex-shrink-0 logo-container group">
                                @if (isset($logo['url']) && !empty($logo['url']))
                                <a href="{{ $logo['url'] }}" target="_blank" rel="noopener" class="block">
                                    <img src="{{ isset($logo['path']) ? asset('storage/' . $logo['path']) : asset('img/dummy-image/dummy_192x48.png') }}"
                                        alt="{{ $logo['alt'] ?? 'Partner logo' }}"
                                        class="logo-image h-8 sm:h-10 lg:h-12 w-auto max-w-32 sm:max-w-40 lg:max-w-48 object-contain opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all duration-300"
                                        loading="lazy" />
                                </a>
                                @else
                                <img src="{{ isset($logo['path']) ? asset('storage/' . $logo['path']) : asset('img/dummy-image/dummy_192x48.png') }}"
                                    alt="{{ $logo['alt'] ?? 'Partner logo' }}"
                                    class="logo-image h-8 sm:h-10 lg:h-12 w-auto max-w-32 sm:max-w-40 lg:max-w-48 object-contain opacity-60 hover:opacity-100 grayscale hover:grayscale-0 transition-all duration-300"
                                    loading="lazy" />
                                @endif
                            </div>
                            @endforeach
                        </div>
                </div>
            </div>

            <!-- Stats or additional info -->
            <div class="mt-12 text-center" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span>Top</span>
                    companies trust our platform worldwide
                </p>
            </div>
        </div>
    </section>

    <style>
        @keyframes scroll {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .animate-scroll {
            animation: scroll 30s linear infinite;
        }

        .animate-scroll:hover {
            animation-play-state: paused;
        }
    </style>
</div>