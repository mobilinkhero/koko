{{-- Make sure to include AOS CSS and JS in your layout --}}
{{-- Add these to your main layout file: --}}


<div>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <div>
        @if (!empty($testimonials) && count($testimonials) > 0)
        <section x-data="{
                currentIndex: 0,
                testimonials: {{ Js::from($testimonials) }},
                interval: null,
                get totalPairs() {
                    return Math.ceil(this.testimonials.length / 2);
                },
                get currentPair() {
                    return [
                        this.testimonials[this.currentIndex * 2] || null,
                        this.testimonials[this.currentIndex * 2 + 1] || null
                    ];
                },
                startCarousel() {
                    if (this.totalPairs > 1) {
                        this.interval = setInterval(() => {
                            this.currentIndex = (this.currentIndex + 1) % this.totalPairs;
                        }, 5000); // Increased timing to allow AOS animations
                    }
                },
                stopCarousel() {
                    if (this.interval) {
                        clearInterval(this.interval);
                        this.interval = null;
                    }
                },
                goToSlide(index) {
                    this.currentIndex = index;
                }
            }" x-init="startCarousel" @mouseenter="stopCarousel" @mouseleave="startCarousel"
            class="text-white py-20 px-6 relative overflow-hidden bg-primary-600"
            style="background-image: url('{{ asset('img/landingpage-image/testimonial-section/testimonial-grid.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
            data-aos="fade-up" data-aos-duration="1000">

            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    <!-- Left Content -->
                    <div class="col-span-1 flex flex-col justify-center" data-aos="fade-right" data-aos-duration="1200"
                        data-aos-delay="200">
                        <h2 class="text-4xl font-bold mb-4 font-display" data-aos="fade-up" data-aos-duration="800"
                            data-aos-delay="400">
                            What our clients say
                        </h2>
                        <p class="text-lg mb-6" data-aos="fade-up" data-aos-duration="800" data-aos-delay="600">
                            Access top-tier group mentoring plans and exclusive professional benefits for your team.
                            🔥
                        </p>

                        <!-- Dots Indicator -->
                        <template x-if="totalPairs > 1">
                            <div class="flex space-x-2" data-aos="fade-up" data-aos-duration="600" data-aos-delay="800">
                                <template x-for="(pair, index) in Array.from({length: totalPairs})" :key="index">
                                    <button @click="goToSlide(index)"
                                        :class="currentIndex === index ? 'bg-white scale-110' : 'bg-white/40'"
                                        class="w-3 h-3 rounded-full transition-all duration-300 hover:bg-white/70 hover:scale-105 transform">
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Testimonials Carousel -->
                    <div class="col-span-2 relative" data-aos="fade-left" data-aos-duration="1200" data-aos-delay="400">
                        <!-- Fixed height container to prevent layout shift -->
                        <div class="min-h-[240px] relative">
                            <template x-for="(pair, pairIndex) in Array.from({length: totalPairs})" :key="pairIndex">
                                <div x-show="currentIndex === pairIndex"
                                    x-transition:enter="transition ease-out duration-700"
                                    x-transition:enter-start="opacity-0 transform translate-x-8 scale-95"
                                    x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
                                    x-transition:leave="transition ease-in duration-400"
                                    x-transition:leave-start="opacity-100 transform translate-x-0 scale-100"
                                    x-transition:leave-end="opacity-0 transform -translate-x-8 scale-95"
                                    class="absolute inset-0">

                                    <div class="grid md:grid-cols-2 gap-6 grid-cols-1 h-full">
                                        <!-- First testimonial in pair -->
                                        <template x-if="testimonials[pairIndex * 2]">
                                            <div class="bg-white/95 backdrop-blur-sm text-gray-800 p-6 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 hover:scale-105 group flex flex-col h-full"
                                                :data-aos="pairIndex === 0 ? 'zoom-in' : ''"
                                                :data-aos-duration="pairIndex === 0 ? '800' : ''"
                                                :data-aos-delay="pairIndex === 0 ? '600' : ''">

                                                <!-- Quote icon with animation -->
                                                <div class="flex items-start mb-3">
                                                    <div class="relative">
                                                        <svg class="w-8 h-8 text-primary-500 mr-2 flex-shrink-0 transform group-hover:scale-110 transition-transform duration-300"
                                                            fill="currentColor" viewBox="0 0 24 24">
                                                            <path
                                                                d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z" />
                                                        </svg>
                                                        <!-- Subtle glow effect -->
                                                        <div
                                                            class="absolute inset-0 w-8 h-8 bg-primary-400 rounded-full opacity-0 group-hover:opacity-20 blur-sm transition-opacity duration-300">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Testimonial text - flexible space -->
                                                <div class="flex-1 ">
                                                    <p class="mb-6 text-gray-700 leading-relaxed line-clamp-4 group-hover:text-gray-800 transition-colors duration-300"
                                                        x-text="testimonials[pairIndex * 2].testimonial"></p>
                                                </div>

                                                <!-- Author info - fixed at bottom -->
                                                <div class="flex items-center space-x-3 mt-auto pt-2">
                                                    <div class="relative flex-shrink-0">
                                                        <img :src="testimonials[pairIndex * 2].profile_image ?
                                                                '{{ asset('storage') }}/' + testimonials[pairIndex * 2]
                                                                .profile_image :
                                                                '{{ asset('img/user-placeholder.jpg') }}'"
                                                            :alt="testimonials[pairIndex * 2].name"
                                                            class="w-12 h-12 rounded-full object-cover border-2 border-primary-200 group-hover:border-primary-300 transition-all duration-300 transform group-hover:scale-110">
                                                        <!-- Profile image glow -->
                                                        <div
                                                            class="absolute inset-0 w-12 h-12 rounded-full bg-primary-400 opacity-0 group-hover:opacity-20 blur-sm transition-opacity duration-300">
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="transform group-hover:translate-x-1 transition-transform duration-300 min-w-0 flex-1">
                                                        <p class="font-semibold text-gray-900 group-hover:text-primary-900 transition-colors duration-300 truncate"
                                                            x-text="testimonials[pairIndex * 2].name"></p>
                                                        <p class="text-sm text-gray-600 group-hover:text-gray-700 transition-colors duration-300 truncate"
                                                            x-text="testimonials[pairIndex * 2].position"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Second testimonial in pair -->
                                        <template x-if="testimonials[pairIndex * 2 + 1]">
                                            <div class="bg-white/95 backdrop-blur-sm text-gray-800 p-6 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 hover:scale-105 group"
                                                :data-aos="pairIndex === 0 ? 'zoom-in' : ''"
                                                :data-aos-duration="pairIndex === 0 ? '800' : ''"
                                                :data-aos-delay="pairIndex === 0 ? '800' : ''">

                                                <!-- Quote icon with animation -->
                                                <div class="flex items-start mb-4">
                                                    <div class="relative">
                                                        <svg class="w-8 h-8 text-primary-500 mr-2 flex-shrink-0 transform group-hover:scale-110 transition-transform duration-300"
                                                            fill="currentColor" viewBox="0 0 24 24">
                                                            <path
                                                                d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z" />
                                                        </svg>
                                                        <!-- Subtle glow effect -->
                                                        <div
                                                            class="absolute inset-0 w-8 h-8 bg-primary-400 rounded-full opacity-0 group-hover:opacity-20 blur-sm transition-opacity duration-300">
                                                        </div>
                                                    </div>
                                                </div>

                                                <p class="mb-6 text-gray-700 leading-relaxed line-clamp-4 group-hover:text-gray-800 transition-colors duration-300"
                                                    x-text="testimonials[pairIndex * 2 + 1].testimonial"></p>

                                                <div class="flex items-center space-x-3 mt-auto">
                                                    <div class="relative">
                                                        <img :src="testimonials[pairIndex * 2 + 1].profile_image ?
                                                                '{{ asset('storage') }}/' + testimonials[pairIndex * 2 +
                                                                    1].profile_image :
                                                                '{{ asset('/img/user-placeholder.jpg') }}'"
                                                            :alt="testimonials[pairIndex * 2 + 1].name"
                                                            class="w-12 h-12 rounded-full object-cover border-2 border-primary-200 group-hover:border-primary-300 transition-all duration-300 transform group-hover:scale-110">
                                                        <!-- Profile image glow -->
                                                        <div
                                                            class="absolute inset-0 w-12 h-12 rounded-full bg-primary-400 opacity-0 group-hover:opacity-20 blur-sm transition-opacity duration-300">
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="transform group-hover:translate-x-1 transition-transform duration-300">
                                                        <p class="font-semibold text-gray-900 group-hover:text-primary-900 transition-colors duration-300"
                                                            x-text="testimonials[pairIndex * 2 + 1].name"></p>
                                                        <p class="text-sm text-gray-600 group-hover:text-gray-700 transition-colors duration-300"
                                                            x-text="testimonials[pairIndex * 2 + 1].position"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating decoration elements -->
            <div class="absolute top-10 left-10 w-20 h-20 bg-white/10 rounded-full blur-xl animate-pulse"
                data-aos="fade-in" data-aos-duration="2000" data-aos-delay="1000"></div>
            <div class="absolute bottom-10 right-10 w-16 h-16 bg-primary-300/20 rounded-full blur-lg animate-pulse"
                data-aos="fade-in" data-aos-duration="2000" data-aos-delay="1200"></div>
        </section>
        @else
        <section class="bg-gray-50 dark:bg-gray-800" data-aos="fade-up" data-aos-duration="800">
            <div class="max-w-screen-xl px-4 py-8 mx-auto text-center lg:py-24 lg:px-6">
                <p class="text-gray-500 dark:text-gray-400">No testimonials available</p>
            </div>
        </section>
        @endif
    </div>

    {{-- Initialize AOS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS with custom settings
            AOS.init({
                duration: 800,
                easing: 'ease-out-cubic',
                once: true, // Animation happens only once
                offset: 100, // Trigger animations 100px before element comes into view
                delay: 0,
                anchorPlacement: 'top-bottom',
                // Disable on mobile for better performance if needed
                disable: function() {
                    return window.innerWidth < 768;
                }
            });

            // Refresh AOS when Alpine.js updates the DOM
            window.addEventListener('alpine:initialized', () => {
                setTimeout(() => {
                    AOS.refresh();
                }, 100);
            });
        });
    </script>

    {{-- Custom CSS for additional animations --}}
    <style>
        /* Custom keyframe animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes glow {

            0%,
            100% {
                box-shadow: 0 0 5px rgba(99, 102, 241, 0.3);
            }

            50% {
                box-shadow: 0 0 20px rgba(99, 102, 241, 0.6);
            }
        }

        /* Add floating animation to testimonial cards */
        .group:hover {
            animation: float 3s ease-in-out infinite;
        }

        /* Custom AOS animations */
        [data-aos="zoom-in-up"] {
            transform: translate3d(0, 100px, 0) scale(0.6);
            opacity: 0;
        }

        [data-aos="zoom-in-up"].aos-animate {
            transform: translate3d(0, 0, 0) scale(1);
            opacity: 1;
        }

        /* Staggered animation for dots */
        .flex>button {
            transition: all 0.3s ease;
        }

        .flex>button:nth-child(1) {
            transition-delay: 0.1s;
        }

        .flex>button:nth-child(2) {
            transition-delay: 0.2s;
        }

        .flex>button:nth-child(3) {
            transition-delay: 0.3s;
        }

        .flex>button:nth-child(4) {
            transition-delay: 0.4s;
        }

        .flex>button:nth-child(5) {
            transition-delay: 0.5s;
        }
    </style>
</div>