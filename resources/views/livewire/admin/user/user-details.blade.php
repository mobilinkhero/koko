<div class="relative">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto space-y-8">
            {{-- Profile Card --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-300 rounded-2xl overflow-hidden">
                {{-- Gradient Header --}}
                <div class="h-1 w-full bg-gradient-to-r from-primary-500 to-purple-600"></div>
                <div class="p-8 relative">
                    {{-- Profile Header --}}
                    <div
                        class="flex flex-col sm:flex-row items-center sm:items-start space-y-6 sm:space-y-0 sm:space-x-6 mb-6 pb-6 border-b border-gray-200 dark:border-slate-700">
                        {{-- Profile Image --}}
                        <div class="relative">
                            <img src="{{ $user->avatar && Storage::disk('public')->exists($user->avatar)
                      ? asset('storage/' . $user->avatar)
                      : asset('img/user-placeholder.jpg') }}" alt="{{ $user->firstname }} {{ $user->lastname }}"
                                class="w-28 h-28 rounded-full object-cover border-4 border-primary-500 shadow-lg glightbox cursor-pointer">
                            @if ($user->is_admin || !empty($user->role_id))
                            <span
                                class="absolute bottom-1 right-1 bg-primary-500 text-white text-xs px-2 py-1 rounded-full">
                                {{ $user->is_admin ? 'Admin' : $user->getRoleNames()->first() }}
                            </span>
                            @endif
                        </div>

                        {{-- User Info --}}
                        <div class="sm:text-left text-center">
                            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-2 truncate w-96">
                                {{ $user->firstname }} {{ $user->lastname }}
                            </h2>
                            <div
                                class="flex items-center justify-center sm:justify-start space-x-2 text-slate-600 dark:text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                                <span class="break-all">{{ $user->email }}</span>
                            </div>
                        </div>

                    </div>

                    {{-- User Details Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- Personal Information --}}
                        <div>
                            <h3
                                class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-4 uppercase tracking-wider">
                                {{ t('personal_information') }}
                            </h3>
                            <div class="space-y-3">
                                {{-- First Name --}}
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                        {{ t('firstname') }}
                                    </p>
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate w-72">
                                        {{ $user->firstname }}
                                    </p>
                                </div>

                                {{-- Last Name --}}
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                        {{ t('lastname') }}
                                    </p>
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate w-72">
                                        {{ $user->lastname }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Contact Information --}}
                        <div>
                            <h3
                                class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-4 uppercase tracking-wider">
                                {{ t('contact_information') }}
                            </h3>
                            <div class="space-y-3">
                                {{-- Phone --}}
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                        {{ t('phone') }}
                                    </p>
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                        {{ $user->phone ?? 'N/A' }}
                                    </p>
                                </div>

                                {{-- Email --}}
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                        {{ t('email') }}
                                    </p>
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200 break-all">
                                        {{ $user->email }}
                                    </p>
                                </div>

                                {{-- Default Language --}}
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                        {{ t('default_language') }}
                                    </p>
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                        {{ $user->default_language ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
