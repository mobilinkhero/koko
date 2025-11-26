<div>
    @if ($faqs->count() > 0)
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <h2 class="mb-4 text-3xl text-center font-extrabold tracking-tight text-gray-900 dark:text-white font-display">
            {{ $themeSettings['theme.faq_section_title'] ?: 'Default Faq Title' }}
        </h2>
        <p class="mb-5 font-light text-center text-gray-500 sm:text-lg dark:text-gray-400">
            {{ $themeSettings['theme.faq_section_subtitle'] ?: 'Default Faq SubTitle' }}
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($faqs as $faq)
            <div class="p-4">
                <x-accordion>
                    <x-slot:title>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ $faq->question }}
                        </h2>
                    </x-slot:title>

                    <x-slot:content>
                        <div
                            class="border-b py-6 border-primary-600 dark:border-primary-400 text-gray-500 dark:text-gray-400">
                            {!! nl2br(e($faq->answer)) !!}
                        </div>
                    </x-slot:content>
                </x-accordion>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif