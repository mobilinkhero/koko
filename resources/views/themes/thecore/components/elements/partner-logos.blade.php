<section class="bg-white dark:bg-gray-900">
    <div
        class="max-w-screen-xl px-4 pb-8 mx-auto lg:pb-16  [mask-image:_linear-gradient(to_right,transparent_0,_black_128px,_black_calc(100%-128px),transparent_100%)]">
        <div class="flex gap-8 overflow-hidden group ">
            <div class="relative font-inter antialiased">


                <div class="w-full max-w-5xl mx-auto px-4 md:px-6 ">
                    <div class="text-center">
                        <!-- Logo Carousel animation  -->
                        <div x-data="{}" x-init="$nextTick(() => {
                            let ul = $refs.logos;
                            ul.insertAdjacentHTML('afterend', ul.outerHTML);
                            ul.nextSibling.setAttribute('aria-hidden', 'true');
                        })"
                            class="inline-flex flex-nowrap overflow-hidden [mask-image:_linear-gradient(to_right,transparent_0,_black_128px,_black_calc(100%-128px),transparent_100%)]">
                            <ul x-ref="logos"
                                class="flex items-center justify-center md:justify-start [&_li]:mx-8 [&_img]:max-w-none animate-infinite-scroll">
                                {{-- <li>
                                    <img src="https://cruip-tutorials.vercel.app/logo-carousel/facebook.svg"
                                        alt="Facebook" />
                                </li> --}}
                                <li>
                                    <img src="{{ asset('img/128x36.svg') }}" alt="image" />
                                </li>
                                <li>
                                    <img src="{{ asset('img/128x36.svg') }}" alt="image" />
                                </li>
                                <li>
                                    <img src="{{ asset('img/128x36.svg') }}" alt="image" />
                                </li>
                                <li>
                                    <img src="{{ asset('img/128x36.svg') }}" alt="image" />
                                </li>
                                <li>
                                    <img src="{{ asset('img/128x36.svg') }}" alt="image" />
                                </li>
                                <li>
                                    <img src="{{ asset('img/128x36.svg') }}" alt="image" />
                                </li>
                            </ul>
                        </div>
                        <!-- End: Logo Carousel animation  -->



                    </div>

                </div>
            </div>
        </div>
</section>
