<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('themes.thecore.components.elements.head')
</head>

<body class="flex flex-col min-h-screen" x-data="{ activeSection: 'home' }" x-init="window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('section');
    sections.forEach(section => {
        const rect = section.getBoundingClientRect();
        if (rect.top <= window.innerHeight / 2 && rect.bottom >= window.innerHeight / 2) {
            activeSection = section.id;
        }
    });
});">

    <!-- Navigation menu Start block -->
    @include('themes.thecore.components.elements.menu')
    <!-- Navigation menu End block -->

    <!-- Hero section Start block -->
    <section id="home">
        <livewire:frontend.hero-section />
    </section>
    <!-- Hero section End block -->

    <!-- Partner logo's Start block -->
    <livewire:frontend.partner-logos />
    {{-- @include('themes.thecore.components.elements.partner-logos') --}}
    <!-- Partner logo's End block -->

    <!-- Features Start block -->
    <section class="bg-gray-50 dark:bg-gray-800" id="features">
        <div class="max-w-screen-xl px-4 py-8 mx-auto space-y-12 lg:space-y-20 lg:py-24 lg:px-6">
            <!-- Row 1 Start-->
            <livewire:frontend.unique-feature />
            <!-- Row 1 End-->

            <!-- Row 2 Start-->
            <livewire:frontend.feature />
            <!-- Row 2 End-->
            <!-- Row 2 Start-->
            <livewire:frontend.feature-two />
            <!-- Row 2 End-->
             <livewire:frontend.feature-three />
        </div>
    </section>
    <!--Features End block -->

    <!-- Testimonials start block -->
    <livewire:frontend.testimonials />
    <!-- Testimonials End block -->

    <!-- Pricing plans Start block -->
    <section class="bg-white dark:bg-gray-900" id="pricing">
        <livewire:frontend.pricing-plans />
    </section>
    <!-- Pricing plans End block -->

    <!--FAQs Start block -->
    <section id="faq">
        <livewire:frontend.faq-list />
    </section>
    <!-- FAQs End block -->

    <!-- Footer Start block -->
    @include('themes.thecore.components.elements.footer')
    <!-- Footer End block -->
    @livewireScripts
</body>

</html>
