<x-app-layout>
    <link rel="stylesheet" href="{{ asset('location/leaflet.css') }}" crossorigin="" />
    <x-slot:title>
        {{ t('chat') }}
    </x-slot:title>
    <div>
        @if (empty(get_tenant_setting_from_db('whatsapp', 'is_whatsmark_connected')) ||
        empty(get_tenant_setting_from_db('whatsapp', 'wm_default_phone_number')))
        <x-disconnected-account />
        @elseif (empty(get_tenant_setting_from_db('pusher', 'app_id')) ||
        empty(get_tenant_setting_from_db('pusher', 'app_key')) ||
        empty(get_tenant_setting_from_db('pusher', 'app_secret')) ||
        empty(get_tenant_setting_from_db('pusher', 'cluster')))
        <div class="flex items-center justify-center h-[calc(100vh_-_90px)]">
            <div class="max-w-md mx-auto my-8 overflow-hidden bg-white dark:bg-gray-800 text-gray-900">
                <div
                    class="relative overflow-hidden rounded-xl shadow-xl transition-all duration-500 ease-in-out bg-white dark:bg-gray-800 dark:text-gray-300">

                    <!-- Card content -->
                    <div class="relative rounded-xl overflow-hidden">
                        <!-- Header -->
                        <div class="flex items-center p-4 group t">
                            <div
                                class="flex-shrink-0 p-2 rounded-full   bg-info-100 text-info-600 dark:bg-info-900/30 dark:text-info-300">
                                <x-heroicon-o-information-circle class="w-6 h-6" />
                            </div>
                            <h3 class="ml-3 text-lg font-semibold">{{ t('pusher_account_setup') }} </h3>
                        </div>

                        <!-- Content area -->
                        <div class="px-5 pb-5">
                            <div class="mb-4 transition-all duration-500 delay-100 opacity-100 transform translate-y-0">
                                <p class="transition-colors duration-300 text-gray-700 dark:text-gray-300">
                                    {{ t('pusher_account_setup_description') }}
                                </p>
                            </div>

                            <!-- Steps -->
                            <div
                                class="space-y-3 transition-all duration-500 delay-300 opacity-100 transform translate-y-0">

                                <!-- Step 1 -->
                                <a href="{{ tenant_route('tenant.settings.pusher') }}"
                                    class="block group relative overflow-hidden rounded-lg transition-all duration-300 bg-gray-50 hover:bg-info-50 dark:bg-gray-700/50 dark:hover:bg-gray-700">

                                    <div
                                        class="absolute inset-0 bg-gradient-to-r from-info-400/20 via-primary-400/20 to-purple-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 dark:group-hover:opacity-30">
                                    </div>

                                    <div class="relative p-4 flex items-start">
                                        <div
                                            class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full mr-3 transition-all duration-300 transform group-hover:scale-110 group-active:scale-95 bg-info-100 text-info-600 dark:bg-info-900/50 dark:text-info-300">
                                            <span>1</span>
                                        </div>
                                        <div class="flex-grow">
                                            <h4
                                                class="font-medium transition-colors duration-300 text-gray-700 dark:text-gray-200">
                                                {{ t('access_system_settings') }} </h4>
                                            <p
                                                class="text-sm mt-1 transition-colors duration-300 text-gray-500 dark:text-gray-400">
                                                {{ t('navigate_to_whatsmark_system') }} </p>
                                        </div>
                                        <span
                                            class="flex-shrink-0 ml-2 transition-all duration-300 transform group-hover:translate-x-1 text-info-500 dark:text-info-300">
                                            <x-heroicon-o-arrow-right class="w-4 h-4" />

                                        </span>
                                    </div>
                                </a>

                                <!-- Step 2 -->
                                <a href="https://docs.corbitaltech.dev/products/whatsmark-saas/tenant" target="_blank"
                                    class="block group relative overflow-hidden rounded-lg transition-all duration-300 bg-gray-50 hover:bg-info-50 dark:bg-gray-700/50 dark:hover:bg-gray-700">

                                    <div
                                        class="absolute inset-0 bg-gradient-to-r from-info-400/20 via-primary-400/20 to-purple-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 dark:group-hover:opacity-30">
                                    </div>

                                    <div class="relative p-4 flex items-start">
                                        <div
                                            class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full mr-3 transition-all duration-300 transform group-hover:scale-110 group-active:scale-95 bg-info-100 text-info-600 dark:bg-info-900/50 dark:text-info-300">
                                            <span>2</span>
                                        </div>
                                        <div class="flex-grow">
                                            <h4
                                                class="font-medium transition-colors duration-300 text-gray-700 dark:text-gray-200">
                                                {{ t('follow_documentation') }} </h4>
                                            <p
                                                class="text-sm mt-1 transition-colors duration-300 text-gray-500 dark:text-gray-400">
                                                {{ t('read_the_whatsmark_documentation') }} </p>
                                        </div>
                                        <span
                                            class="flex-shrink-0 ml-2 transition-all duration-300 transform group-hover:translate-x-1 text-info-500 dark:text-info-300">
                                            <x-heroicon-o-arrow-right class="w-4 h-4" />
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Notification text -->
                <div
                    class="text-center mt-3 text-xs transition-colors duration-300 dark:bg-gray-700/50 text-gray-500 dark:text-gray-300">
                    <p>{{ t('real_time_notification_require_pusher_integration') }} </p>
                </div>

            </div>
        </div>
        @else
        <div x-data="chatApp({{ json_encode($chats) }})" x-init="initialize()"
            class="flex gap-2 p-2 relative sm:h-[calc(100vh_-_100px)] h-full"
            :class="{ 'min-h-[999px]': isShowChatMenu }">

            <!-- Sidebar -->
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-4 flex-none max-w-[24rem] w-full absolute xl:relative z-10 space-y-4 h-full hidden xl:block overflow-hidden"
                :class="isShowChatMenu ? '!block ' : ''">
                <div class="w-full">
                    <div class="flex items-center justify-between rounded-lg relative" x-data="{ filterPopup: false }">
                        <!-- Header with User Info & Dropdown Menu -->
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="flex-none">
                                    <img src="{{ checkRemoteFile(get_tenant_setting_from_db('whatsapp', 'wm_profile_picture_url')) ? get_tenant_setting_from_db('whatsapp', 'wm_profile_picture_url') : asset('img/avatar-agent.svg') }}"
                                        class="rounded-full h-12 w-12 object-cover" />
                                </div>
                                <div class="mx-3">
                                    <p x-show="selectedUser"
                                        class="font-normal text-sm text-gray-800 dark:text-gray-200">
                                        <span>{{ t('from') }}</span>
                                        <span x-text="selectedUser?.wa_no ? '+' + selectedUser.wa_no : ''"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Funnel Button -->
                        <div class="flex items-center">
                            <button class="tab-button px-2 py-1 rounded-md text-sm font-medium duration-200 ease-in-out
                     bg-primary-50 hover:bg-primary-100 text-primary-600
                     dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200" @click="filterPopup = !filterPopup">
                                <x-heroicon-s-funnel class="w-5 h-5 inline-block" />
                            </button>
                        </div>


                        <!-- Filter Popup (absolute) -->
                        <div x-show="filterPopup" x-cloak x-transition
                            class="absolute right-0 top-full mt-2 w-full bg-primary-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md z-50">

                            <div class="p-3 flex flex-col gap-2 w-full">
                                <div class="flex gap-2 w-full">
                                    <!-- Relation Type -->
                                    <div class="w-full">
                                        <label for="relationType"
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ t('relation_type') }}
                                        </label>
                                        <select id="relationType" x-model="reltypeFilter"
                                            class="block w-full rounded-md border-gray-300 shadow-sm px-2 py-1.5
                               focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50
                               dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:focus:border-primary-400 dark:focus:ring-primary-500 text-sm">
                                            <option value="">{{ t('all') }}</option>
                                            <template x-for="relType in rel_types" :key="relType.key">
                                                <option :value="relType.key" x-text="relType.value"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Agents -->
                                    <div class="w-full">
                                        <label for="agents"
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ t('agents') }}
                                        </label>
                                        <select id="agents" x-model="agentsFilter"
                                            class="block w-full rounded-md border-gray-300 shadow-sm px-2 py-1.5
                             focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50
                             dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:focus:border-primary-400 dark:focus:ring-primary-500 text-sm">
                                            <option value="">{{ t('all') }}</option>
                                            @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="flex gap-2 w-full">
                                    <!-- Read Status -->
                                    <div class="w-full">
                                        <label for="filterReadStatus"
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ t('read_status') }}
                                        </label>
                                        <select id="filterReadStatus" x-model="selectedReadStatus"
                                            class="block w-full rounded-md border-gray-300 shadow-sm px-2 py-1.5
                              focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50
                              dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:focus:border-primary-400 dark:focus:ring-primary-500 text-sm">
                                            <option value="">{{ t('all') }}</option>
                                            <option value="unread">{{ t('unread_only') }}</option>
                                            <option value="read">{{ t('read_only') }}</option>
                                        </select>
                                    </div>
                                    <div class="w-full"
                                        x-show="reltypeFilter === 'customer' || reltypeFilter === 'lead'">
                                        <label for="filterGroups"
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ t('groups') }}
                                        </label>
                                        <select id="filterGroups" x-model="selectedGroup"
                                            class="block w-full rounded-md border-gray-300 shadow-sm px-2 py-1.5
                                focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50
                                dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:focus:border-primary-400 dark:focus:ring-primary-500 text-sm">
                                            <option value="">{{ t('all') }}</option>
                                            @foreach($groups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <!-- Groups, Status, Sources -->
                                <div class="flex gap-2 w-full">


                                    <div class="w-full"
                                        x-show="reltypeFilter === 'customer' || reltypeFilter === 'lead'">
                                        <label for="filterStatus"
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ t('status') }}
                                        </label>
                                        <select id="filterStatus" x-model="selectedStatus"
                                            class="block w-full rounded-md border-gray-300 shadow-sm px-2 py-1.5
                                focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50
                                dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:focus:border-primary-400 dark:focus:ring-primary-500 text-sm">
                                            <option value="">{{ t('all') }}</option>
                                            @foreach($statuses as $status)
                                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="w-full"
                                        x-show="reltypeFilter === 'customer' || reltypeFilter === 'lead'">
                                        <label for="filterSources"
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ t('sources') }}
                                        </label>
                                        <select id="filterSources" x-model="selectedSource"
                                            class="block w-full rounded-md border-gray-300 shadow-sm px-2 py-1.5
                                focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50
                                dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:focus:border-primary-400 dark:focus:ring-primary-500 text-sm">
                                            <option value="">{{ t('all') }}</option>
                                            @foreach($sources as $source)
                                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="border-t bg-slate-50 dark:bg-transparent rounded-b-md border-slate-300 px-3 py-2 sm:px-4 dark:border-slate-600">
                                <div class="flex justify-end">
                                    <button @click="handleAllFilters(); filterPopup = false"
                                        class="px-3 py-1.5 bg-primary-600 text-white rounded-md text-xs font-medium hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        {{ t('apply') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="w-full">
                    <!-- Select Dropdown -->
                    <select id="selectedWaNo" x-on:change="filterChats()"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 tom-select"
                        x-model="selectedWaNo">
                        <template x-for="(wa_no, index) in uniqueWaNos()" :key="index">
                            <option :value="wa_no" :selected="selectedWaNo === wa_no" x-text="wa_no">
                            </option>
                        </template>
                        <option value="*">{{ t('all_chats') }}</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="relative" x-cloak>
                    <input type="text" id="searchText" placeholder="{{ t('searching') }}..." autocomplete="off"
                        class="block mt-1 w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600"
                        x-model="searchText" x-on:input="searchChats()" />
                    <div
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 focus:text-primary-500">
                        <x-heroicon-m-magnifying-glass class="w-4 h-4" />
                    </div>
                </div>


                <div x-show="noResultsMessage" x-html="noResultsMessage" class="text-danger-500 text-xs" x-cloak>
                </div>

                <!-- Divider -->
                <div class="h-px w-full border-b border-[#e0e6ed] dark:border-slate-600"></div>
                <div class="!mt-0">
                    <div class="chat-users relative h-full min-h-[100px] sm:h-[calc(100vh_-_310px)] space-y-0.5 pr-3.5 pl-3.5 -mr-3.5 -ml-3.5 overflow-y-auto"
                        @scroll="onSidebarScroll($event)" x-ref="chatSidebar" x-cloak>
                        <template x-for="(chat, chatIndex) in sortedChats" :key="`chat-${chat.id}-${chatIndex}`"
                            x-cloak>
                            <div class="w-full cursor-pointer flex justify-between items-center border-b px-2 py-3 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-[#050b14] rounded-md dark:hover:text-primary-500 hover:text-primary-500"
                                :class="{
                                        'border border-primary-300 bg-primary-50 dark:bg-[#050b14] dark:text-primary-500 text-primary-600': selectedUser &&
                                            selectedUser.id === chat.id
                                    }" x-on:click="selectChat(chat)">
                                <div class="flex-1">
                                    <div class="flex items-center ">
                                        <div class="flex-shrink-0 relative">
                                            <div
                                                class="rounded-full h-10 w-10 flex items-center justify-center bg-primary-100 text-primary-700 text-sm font-medium">
                                                <span
                                                    x-text="chat.name.split(' ').map(word => word[0]).join('').substring(0, 2).toUpperCase()"></span>
                                            </div>
                                        </div>

                                        <div class="mx-3 flex flex-col gap-1 justify-start items-start w-full relative">
                                            <!-- Name and Type in One Line -->
                                            <div class="flex items-center justify-between w-full">
                                                <div class="flex items-center justify-start">
                                                    <p class="font-normal text-xs truncate max-w-[100px]"
                                                        x-text="chat.name" x-bind:data-tippy-content="chat.receiver_id">
                                                    </p>
                                                    <span :class="{
                                                                'bg-violet-100 text-purple-800': chat
                                                                    .type === 'lead',
                                                                'bg-danger-100 text-danger-800': chat.type === 'customer',
                                                                'bg-warning-100 text-warning-800': chat
                                                                    .type === 'guest',
                                                                'bg-gray-100 text-gray-800': !['lead', 'customer',
                                                                        'guest'
                                                                    ]
                                                                    .includes(selectedUser?.type)
                                                            }"
                                                        class="inline-block ml-2 text-xs font-meduim px-2 rounded">
                                                        <span x-text="chat.type"></span>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="font-normal whitespace-nowrap text-xs">
                                                        <p x-text="formatLastMessageTime(chat.time_sent)"></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <p class="text-xs text-gray-500 truncate max-w-[200px]"
                                                x-text="sanitizeLastMessage(chat.last_message)">
                                            </p>
                                            <span x-show="countUnreadMessages(chat.id) > 0 && !chat.hideUnreadCount"
                                                class="absolute sm:left-[235px] left-[210px] top-5 flex items-center justify-center w-5 h-5 text-xs font-normal text-white bg-primary-600 rounded-full cursor-pointer"
                                                x-text="countUnreadMessages(chat.id)">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <!-- Sidebar End-->
            <!-- Overlay Sidebar-->
            <div class="bg-black/60 z-[5] w-full h-full absolute rounded-xl hidden"
                x-bind:class="{ '!block xl:!hidden': isShowChatMenu }" x-on:click="isShowChatMenu = !isShowChatMenu">
            </div>
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-0 flex-1 relative">
                <!-- When no user is selected -->
                <div x-show="!isShowUserChat" class="h-full" x-cloak>
                    <div class="flex items-center justify-center h-full relative p-4">
                        <button type="button"
                            class="xl:hidden absolute top-4 left-4 right-4 hover:text-primary-500 text-gray-500 dark:text-slate-400"
                            x-on:click="isShowChatMenu = !isShowChatMenu">
                            <!-- Menu Icon -->
                            <x-heroicon-s-bars-3 class="w-6 h-6" />
                        </button>
                        <div class="py-8 flex items-center justify-center flex-col" x-cloak>
                            <div
                                class="w-[280px] md:w-[430px] mb-8 h-[calc(100vh_-_320px)] min-h-[120px] text-black dark:text-slate-400">
                                <!-- Light mode image -->
                                <img src="{{ asset('/img/chat/chat-white.svg') }}" alt="light mode image"
                                    class="w-full h-full dark:hidden" />

                                <!-- Dark mode image -->
                                <img src="{{ asset('/img/chat/chat-black.svg') }}" alt="dark mode image"
                                    class="w-full h-full hidden dark:block" />
                            </div>

                            <!-- Instruction text -->
                            <div
                                class="flex justify-center item-center gap-4 p-2 font-semibold rounded-md max-w-[190px] mx-auto dark:text-gray-400">
                                <span>{{ t('click_user_to_chat') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat detail: Only visible when a user is selected -->
                <div x-show="isShowUserChat && selectedUser" class="relative h-full" x-cloak>
                    <!-- Header Section -->
                    <x-dynamic-alert x-show="sendingErrorMessage" type="danger">
                        <b>{{ t('error') }}</b>
                        <span x-text="sendingErrorMessage"></span>
                    </x-dynamic-alert>
                    <div class="flex justify-between items-center p-4">
                        <div class="flex items-center space-x-2 dark:space-x-reverse">
                            <!-- Mobile Menu Toggle Button -->
                            <button type="button"
                                class="xl:hidden hover:text-primary-500 text-gray-500 dark:text-slate-400"
                                x-on:click="isShowChatMenu = !isShowChatMenu">
                                <!-- Menu Icon -->
                                <x-heroicon-s-bars-3 class="w-6 h-6" />
                            </button>

                            <!-- User Avatar and Active Indicator -->
                            <div class="relative flex-none">
                                <div
                                    class="rounded-full h-11 w-11 flex items-center justify-center bg-primary-100 text-primary-700 text-sm font-medium">
                                    <span
                                        x-text="(selectedUser?.name ?? 'User').split(' ').map(word => word[0]).join('').substring(0, 2).toUpperCase()"></span>
                                </div>
                            </div>


                            <!-- User Name and Status -->
                            <div class="mx-3">
                                <div class="flex justify-start items-center">
                                    <!-- Display Selected User Name -->
                                    <a target="_blank"
                                        class="font-medium text-sm truncate max-w-[88px] sm:max-w-[185px] text-gray-700 dark:text-gray-200"
                                        x-bind:href="(selectedUser?.type === 'lead' || selectedUser?.type === 'customer') ?
                                            `{{ tenant_route('tenant.contacts.save', ['contactId' => 'CONTACT_ID']) }}`
                                            .replace
                                                ('CONTACT_ID', userInfo?.id || ''): '#'" x-bind:data-tippy-content="(selectedUser?.type === 'lead' || selectedUser?.type === 'customer') ?
                                            '{{ t('click_to_open_leads') }}' :
                                            ''" x-bind:class="(selectedUser?.type === 'lead' || selectedUser?.type === 'customer') ?
                                            'cursor-pointer' :
                                            'pointer-events-none text-gray-400'"
                                        x-text="selectedUser?.name ?? 'Unknown'">
                                    </a>

                                    <!-- Badge for chat type -->
                                    <span :class="{
                                                'bg-violet-100 text-purple-800': selectedUser?.type === 'lead',
                                                'bg-danger-100 text-danger-800': selectedUser?.type === 'customer',
                                                'bg-warning-100 text-warning-800': selectedUser?.type === 'guest',

                                            }" class="inline-block ml-2 text-xs font-normal px-2 rounded"
                                        x-text="selectedUser?.type">
                                    </span>
                                </div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400"
                                    x-text="selectedUser?.receiver_id ?? ''"></p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex sm:gap-3 gap-1 relative">
                            <button x-on:click="messagesSearch = !messagesSearch"
                                class=" text-primary-500 dark:text-gray-200 mr-3 hidden sm:block">
                                <x-heroicon-m-magnifying-glass class="w-5 h-5" />
                            </button>
                            <button type="button"
                                class="relative hover:text-primary-500 text-gray-500 dark:text-slate-400 "
                                x-on:click.stop="showAlert = !showAlert" x-on:click.away="showAlert = false">
                                <!-- Status Indicator -->
                                <span class="flex items-center justify-center">
                                    <span class="absolute h-3 w-3 rounded-full opacity-75" :class="{

                                                'bg-success-500 animate-ping': !overdueAlert
                                            }"></span>
                                    <span class="relative h-3 w-3 rounded-full" :class="{

                                                'bg-success-500': !overdueAlert
                                            }"></span>
                                </span>
                            </button>
                            <!-- Message when overdue alert is true -->
                            <div x-show="showAlert" x-transition
                                class="absolute mt-2 right-[-0.10rem] sm:right-[8.25rem] lg:right-[14.25rem] xl:right-[9.25rem] top-[3.25rem] sm:top-[3.3rem] w-80 sm:w-max p-2 rounded shadow z-10 flex items-center gap-2"
                                :class="{

                                        'bg-success-100 dark:bg-success-900 dark:text-success-400 text-success-700': !
                                            overdueAlert
                                    }">

                                <!-- Heroicon Exclamation centered -->
                                <!-- Icon -->

                                <x-heroicon-o-clock x-show="!overdueAlert"
                                    class="w-6 h-6 text-success-700 dark:text-success-400 flex-shrink-0" />

                                <!-- Message Text -->
                                <div>

                                    <!-- Not Overdue Message -->
                                    <template x-if="!overdueAlert" x-cloak>
                                        <span class="font-normal text-success-700 dark:text-success-400 text-sm">
                                            {{ t('reply_within') }} <span x-text="remainingHours"></span>
                                            {{ t('hours_and') }}
                                            <span x-text="remainingMinutes"></span> {{ t('minutes_remaining') }}
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div x-show="isAdmin == 1" x-html="asignAgentView">
                            </div>
                            <button type="button"
                                class="hover:text-primary-500 text-gray-500 dark:text-slate-400 mt-1 hidden sm:block"
                                x-on:click="isShowUserInfo = true">
                                <x-heroicon-o-information-circle class="mx-auto mb-1 w-6 h-6"
                                    data-tippy-content="{{ t('user_information') }}" />
                            </button>

                            <button x-on:click='handleModal()'
                                class="hover:text-success-700 text-success-500 dark:text-slate-400 mt-1 hidden sm:block">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 32 32"
                                    class="mx-auto mb-1 w-6 h-6" data-tippy-content="{{ t('initiate_chat') }}">
                                    <path
                                        d="M16.01 2.006a13.97 13.97 0 00-12.2 20.96L2 30l7.2-1.8A13.974 13.974 0 1016.01 2.006zm0 25.974c-2.08 0-4.07-.53-5.83-1.53l-.42-.24-4.28 1.07 1.1-4.16-.28-.43A11.96 11.96 0 1116.01 28zm6.41-8.94c-.34-.17-2.01-.99-2.33-1.1-.31-.11-.54-.17-.76.17-.23.34-.88 1.1-1.08 1.32-.2.23-.4.25-.75.08-.34-.17-1.44-.53-2.74-1.7a10.182 10.182 0 01-1.89-2.33c-.2-.34 0-.52.15-.69.15-.16.34-.4.5-.6.17-.2.23-.34.34-.56.12-.23.06-.43 0-.6-.07-.17-.76-1.84-1.04-2.52-.28-.68-.56-.59-.76-.6h-.65c-.22 0-.56.08-.85.4s-1.12 1.1-1.12 2.68 1.15 3.1 1.31 3.32c.17.23 2.27 3.45 5.5 4.83.77.33 1.37.53 1.83.68.77.24 1.46.2 2.01.12.61-.09 1.87-.76 2.13-1.5.27-.74.27-1.37.19-1.5-.07-.13-.3-.2-.63-.36z" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu (Popper) -->
                            <div class="dropdown">
                                <div x-data="{ openDropdown: false }" class="relative">
                                    <button x-on:click="openDropdown = !openDropdown"
                                        class="bg-[#f4f4f4] dark:bg-[#050b14] hover:text-primary-500 w-8 h-8 text-gray-500 dark:text-slate-400 rounded-full flex justify-center items-center">
                                        <x-heroicon-m-ellipsis-vertical class="w-5 h-5"
                                            data-tippy-content="{{ t('more') }}" aria-hidden="true" />
                                    </button>
                                    <ul x-show="openDropdown" x-on:click.away="openDropdown = false"
                                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-2 z-20">
                                        <li class="sm:hidden block">
                                            <button type="button"
                                                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700"
                                                x-on:click="isShowUserInfo = true">
                                                <x-heroicon-o-information-circle class="w-5 h-5" />
                                                <span>{{ t('user_information') }}</span>
                                            </button>
                                        </li>
                                        <li class="sm:hidden block">
                                            <button x-on:click="messagesSearch = true; openDropdown = false"
                                                class="flex items-center w-full gap-2 px-4 py-2 text-sm text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                                                <x-heroicon-m-magnifying-glass class="w-5 h-5" />
                                                <span>{{ t('search') }}</span>
                                            </button>
                                        </li>

                                        <li class="sm:hidden block">
                                            <button x-on:click='handleModal()'
                                                class="flex items-center  gap-2 px-4  text-sm text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                    viewBox="0 0 32 32" class="mx-auto mb-1 w-5 h-5"
                                                    data-tippy-content="{{ t('initiate_chat') }}">
                                                    <path
                                                        d="M16.01 2.006a13.97 13.97 0 00-12.2 20.96L2 30l7.2-1.8A13.974 13.974 0 1016.01 2.006zm0 25.974c-2.08 0-4.07-.53-5.83-1.53l-.42-.24-4.28 1.07 1.1-4.16-.28-.43A11.96 11.96 0 1116.01 28zm6.41-8.94c-.34-.17-2.01-.99-2.33-1.1-.31-.11-.54-.17-.76.17-.23.34-.88 1.1-1.08 1.32-.2.23-.4.25-.75.08-.34-.17-1.44-.53-2.74-1.7a10.182 10.182 0 01-1.89-2.33c-.2-.34 0-.52.15-.69.15-.16.34-.4.5-.6.17-.2.23-.34.34-.56.12-.23.06-.43 0-.6-.07-.17-.76-1.84-1.04-2.52-.28-.68-.56-.59-.76-.6h-.65c-.22 0-.56.08-.85.4s-1.12 1.1-1.12 2.68 1.15 3.1 1.31 3.32c.17.23 2.27 3.45 5.5 4.83.77.33 1.37.53 1.83.68.77.24 1.46.2 2.01.12.61-.09 1.87-.76 2.13-1.5.27-.74.27-1.37.19-1.5-.07-.13-.3-.2-.63-.36z" />
                                                </svg>
                                                <span>{{ t('initiate_chat') }}</span>
                                            </button>
                                        </li>
                                        @if (get_tenant_setting_from_db('whats-mark', 'only_agents_can_chat'))
                                        <li x-show="isAdmin == 1">
                                            <button x-on:click='openSupportAgentModal()'
                                                class="flex items-center w-full gap-2 px-4 py-2 text-sm text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                                                <x-heroicon-o-user-plus class="w-6 h-6" />
                                                <span>{{ t('support_agent') }}</span>
                                            </button>
                                        </li>
                                        @endif
                                        @if (checkPermission('chat.delete'))
                                        <li>
                                            <button x-on:click='isDeleteChatModal = true'
                                                data-tippy-content="{{ t('remove_chat') }}"
                                                class="flex items-center w-full gap-2 px-4 py-2 text-sm text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                                                <x-heroicon-o-trash class="w-5 h-5" />
                                                <span>{{ t('delete') }}</span>
                                            </button>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="h-px w-full border-b border-[#e0e6ed] dark:border-slate-600"></div>

                    <!-- Chat Conversation Section -->
                    <div x-show="loading"
                        class="absolute z-[90] w-full h-full inset-0 items-center justify-center bg-white dark:bg-neutral-800 bg-opacity-50">
                        <svg class="w-8 h-8 absolute top-[50%] right-[36.4rem] animate-spin text-primary-600"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                    <div style="will-change: transform;"
                        class="relative overflow-auto rounded-b-lg h-[calc(100vh_-_150px)] chat-conversation-box bg-stone-100"
                        :class="readOnlyPermission ? 'sm:h-[calc(100vh_-_250px)]' : 'sm:h-[calc(100vh_-_177px)]'"
                        @scroll="selectedUser?.id && checkScrollTop(selectedUser.id)" x-ref="chatContainer">
                        <div class="space-y-5 p-4 px-0 sm:px-20 sm:min-h-[300px] min-h-[400px] sm:mb-8 mb-16"
                            :class="readOnlyPermission ? 'pb-[120px]' : 'pb-0'">

                            <!-- Render messages if available -->
                            <div x-show="selectedUser && selectedUser.messages?.length">
                                <template x-for="(message, index) in selectedUser?.messages ?? []" :key="index">
                                    <div>
                                        <!-- Display Date Divider Between Messages -->
                                        <template
                                            x-if="selectedUser && selectedUser.messages && shouldShowDate(message, selectedUser.messages[index - 1])">

                                            <div class="flex justify-center my-2">
                                                <span
                                                    class="bg-white py-1 px-2 text-xs rounded-md dark:bg-gray-600 dark:text-gray-200"
                                                    x-text="formatDate(message.time_sent)">
                                                </span>
                                            </div>
                                        </template>

                                        <!-- Message Wrapper -->
                                        <div class="flex items-start gap-3">
                                            <div class="flex w-full relative" :class="message.sender_id === selectedUser.wa_no ? 'justify-end' :
                                                        'justify-start'">
                                                <!-- Ellipsis Icon to Open Menu -->
                                                <button x-on:click="toggleMessageOptions(message.id)">
                                                    <x-heroicon-m-ellipsis-vertical
                                                        class="w-5 h-5 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white" />
                                                </button>

                                                <!-- Message Content -->
                                                <div class="p-2 rounded-lg max-w-xl break-words my-2 message-item"
                                                    :data-message-id='message.message_id' :class="{
                                                            'bg-[#c7c8ff] dark:bg-[#2d2454]': message.sender_id ===
                                                                selectedUser.wa_no,
                                                            'bg-white dark:bg-[#273443]': message.sender_id !==
                                                                selectedUser
                                                                .wa_no,
                                                            'bg-[#cbced4] dark:bg-[#3b4348fa]': message.staff_id == 0 &&
                                                                message.sender_id === selectedUser.wa_no
                                                        }">
                                                    <div x-show="message.ref_message_id"
                                                        x-on:click="scrollToMessage(message.ref_message_id)"
                                                        class="bg-neutral-100 dark:bg-gray-500 rounded-lg mb-2 cursor-pointer">
                                                        <div
                                                            class="flex flex-col gap-2 p-2 border-primary-500 border-l-4 rounded">

                                                            <span class="text-gray-700 dark:text-gray-200 text-xs"
                                                                x-html="getOriginalMessage(message.ref_message_id)?.message"></span>
                                                            <template
                                                                x-if="getOriginalMessage(message.ref_message_id)?.url">
                                                                <div>
                                                                    <template
                                                                        x-if="getOriginalMessage(message.ref_message_id)?.type === 'image'">
                                                                        <a :href="getOriginalMessage(message.ref_message_id)
                                                                                ?.url" data-lightbox="image-group"
                                                                            target="_blank">
                                                                            <img :src="getOriginalMessage(message
                                                                                        .ref_message_id)
                                                                                    ?.url"
                                                                                class="rounded-lg max-w-xs max-h-28"
                                                                                alt="Image">
                                                                        </a>
                                                                    </template>

                                                                    <template
                                                                        x-if="getOriginalMessage(message.ref_message_id)?.type === 'video'">
                                                                        <video :src="getOriginalMessage(message
                                                                                        .ref_message_id)
                                                                                    ?.url" controls
                                                                            class="rounded-lg max-w-xs max-h-28"></video>
                                                                    </template>

                                                                    <template
                                                                        x-if="getOriginalMessage(message.ref_message_id)?.type === 'document'">
                                                                        <a :href="getOriginalMessage(message.ref_message_id)
                                                                                ?.url" target="_blank"
                                                                            class="text-info-500 underline">
                                                                            {{ t('download_document') }}
                                                                        </a>
                                                                    </template>

                                                                    <template
                                                                        x-if="getOriginalMessage(message.ref_message_id)?.type === 'audio'">
                                                                        <audio controls class="w-[250px]">
                                                                            <source :src="getOriginalMessage(message
                                                                                        .ref_message_id)?.url"
                                                                                type="audio/mpeg">
                                                                        </audio>
                                                                    </template>
                                                                    <template
                                                                        x-if="getOriginalMessage(message.ref_message_id)?.type === 'interactive'">
                                                                        <span
                                                                            class="text-gray-700 dark:text-gray-200 text-xs"
                                                                            x-html="getOriginalMessage(message.ref_message_id)?.message"></span>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>

                                                    <!-- Message Text -->

                                                    <template x-if="message.type === 'text' &&message.staff_id != 0">
                                                        <p class="text-gray-800 dark:text-white text-sm"
                                                            x-html="formatMessage(message.message)"></p>
                                                    </template>
                                                    <template x-if="message.type === 'text' &&message.staff_id == 0">
                                                        <p class="text-gray-800 dark:text-white text-sm"
                                                            x-html="heighlightMessage(message.message)"></p>
                                                    </template>



                                                    <template x-if="message.type === 'button'">
                                                        <p class="text-gray-800 dark:text-white text-sm"
                                                            x-html="highlightSearch(message.message)"></p>
                                                    </template>

                                                    <template x-if="message.type === 'reaction'">
                                                        <p class="text-gray-800 dark:text-white text-sm"
                                                            x-html="highlightSearch(message.message)"></p>
                                                    </template>

                                                    <template x-if="message.type === 'interactive'">
                                                        <p class="text-gray-800 dark:text-white text-sm"
                                                            x-html="highlightSearch(message.message)"></p>
                                                    </template>

                                                    <!-- Image -->
                                                    <template x-if="message.type === 'image'"
                                                        x-init="$nextTick(() => { window.initGLightbox() })">
                                                        <a :href="message.url" target="_blank" class="glightbox">
                                                            <img :src="message.url" alt="Image"
                                                                class="rounded-lg max-w-xs max-h-28">
                                                        </a>
                                                        <p class="text-gray-600 text-xs mt-2 dark:text-gray-200"
                                                            x-show="message.caption" x-text="message.caption"></p>
                                                    </template>

                                                    <!-- Video -->
                                                    <template x-if="message.type === 'video'"
                                                        x-init="$nextTick(() => { window.initGLightbox() })">
                                                        <a :href="message.url" class="glightbox">
                                                            <video :src="message.url" controls
                                                                class="rounded-lg max-w-xs max-h-28"></video>
                                                        </a>
                                                        <p class="text-gray-600 text-xs mt-2 dark:text-gray-200"
                                                            x-show="message.message" x-text="message.message"></p>
                                                    </template>

                                                    <!-- Document -->
                                                    <template x-if="message.type === 'document'">
                                                        <a :href="message.url" target="_blank"
                                                            class="bg-gray-100 text-success-500 px-3 py-2 rounded-lg flex items-center justify-center text-xs space-x-2 w-full dark:bg-gray-800 dark:text-success-400">
                                                            {{ t('download_document') }}
                                                        </a>
                                                    </template>

                                                    <!-- Audio -->
                                                    <template x-if="message.type === 'audio'">
                                                        <audio id="audioPlayer" controls class="w-[300px]">
                                                            <source :src="message.url" type="audio/mpeg">
                                                        </audio>
                                                        <p class="text-gray-600 text-xs mt-2 dark:text-gray-200"
                                                            x-show="message.message" x-text="message.message"></p>
                                                    </template>

                                                    <!-- Message Timestamp & Status -->
                                                    <div
                                                        class="flex justify-end items-end mt-2 text-xs text-gray-600 dark:text-gray-200">
                                                        <span x-text="formatTime(message.time_sent)"></span>
                                                        <div class="flex justify-end item-center">
                                                            <span x-show="message.sender_id === selectedUser.wa_no"
                                                                class="ml-1">
                                                                <template x-if="message.status === 'sent'">
                                                                    <x-heroicon-o-check
                                                                        class="w-4 h-4 text-gray-500 dark:text-white"
                                                                        title="Sent" />
                                                                </template>

                                                                <template x-if="message.status === 'delivered'">
                                                                    <img src="{{ asset('/img/chat/delivered.png') }}"
                                                                        alt="Delivered-message"
                                                                        class="w-4 h-4 text-gray-500 dark:text-white" />
                                                                </template>

                                                                <template x-if="message.status === 'read'">
                                                                    <img src="{{ asset('/img/chat/double-check-read.png') }}"
                                                                        alt="read-message"
                                                                        class="w-4 h-4 text-cyan-500" />
                                                                </template>

                                                                <template x-if="message.status === 'failed'">
                                                                    <x-heroicon-o-exclamation-circle
                                                                        class="w-4 h-4 text-danger-500"
                                                                        title="Failed" />
                                                                </template>

                                                                <template x-if="message.status === 'deleted'">
                                                                    <x-heroicon-o-trash class="w-4 h-4 text-danger-500"
                                                                        title="Deleted" />
                                                                </template>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <!-- Options Menu -->
                                                    <div x-show="activeMessageId === message.id" x-transition
                                                        x-on:click.away="activeMessageId = null"
                                                        class="absolute top-[-4.5rem] z-10 w-40 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 shadow-lg rounded-lg py-2"
                                                        :class="message.sender_id === selectedUser.wa_no ? 'right-0' :
                                                                'left-0'">
                                                        <ul class="text-sm">
                                                            <div class="flex justify-start items-center gap-2 px-2 py-2 hover:bg-gray-200 hover:text-primary-500 dark:hover:bg-gray-700 cursor-pointer"
                                                                x-on:click="replyToMessage(message)">
                                                                <x-heroicon-c-arrow-path-rounded-square
                                                                    class="w-5 h-5 dark:text-gray-300 text-primary-500" />
                                                                <li class="dark:text-gray-300 text-primary-500">
                                                                    {{ t('reply') }}
                                                                </li>
                                                            </div>
                                                            <div x-on:click.stop="deleteMessage(message.id)"
                                                                class="flex justify-start items-center gap-2 px-2 py-2 hover:bg-gray-200 hover:text-primary-500 dark:hover:bg-gray-700 cursor-pointer">
                                                                <x-heroicon-o-trash
                                                                    class="w-5 h-5 dark:text-gray-300 text-danger-500" />
                                                                <li class="dark:text-gray-300 text-primary-500">
                                                                    {{ t('delete') }}
                                                                </li>
                                                            </div>
                                                        </ul>
                                                    </div>
                                                </div> <!-- End Message Content -->
                                            </div> <!-- End Message Wrapper -->
                                        </div>
                                        <span x-show="message.status_message && message.status_message.length > 0"
                                            class="text-danger-500 text-xs truncate text-right block text-wrap"
                                            x-text="message.status_message">
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <button class="absolute p-2 rounded-full shadow-lg bottom-[9rem] sm:bottom-[10rem] right-4
                            transition-all duration-300 ease-in-out
                            bg-gray-200 hover:bg-gray-300 text-gray-700
                            dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200
                            transform hover:scale-110" x-on:click="scrollToBottom">
                        <x-heroicon-o-arrow-small-down class="w-5 h-5" />
                    </button>
                    <!-- Search Modal -->
                    <div x-show="messagesSearch" x-cloak
                        class="absolute top-[5.5rem] left-1/2 transform -translate-x-1/2 z-50"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        x-init="$watch('messagesSearch', value => { if (value) $nextTick(() => $refs.searchInput.focus()); })">

                        <!-- Search Input -->
                        <div class="sm:w-[480px] w-full px-4">
                            <div class="relative">
                                <input type="text" x-model="searchMessagesText" x-on:input="searchMessages()"
                                    x-ref="searchInput"
                                    class="w-full border shadow rounded-full text-gray-800 border-gray-300 bg-white dark:text-gray-200 dark:border-gray-700 dark:bg-gray-800 outline-none p-2 pr-12"
                                    placeholder="{{ t('search_messages') }}">
                                <div x-show="matchedMessages.length > 0"
                                    class="ml-2 text-sm text-gray-600 dark:text-gray-300">
                                    <span id="search-counter"></span>
                                </div>
                                <button
                                    class="absolute top-[0.2rem] right-[2.5rem] text-primary-400 dark:text-primary-300"
                                    x-on:click="prevMatch" x-show="matchedMessages.length > 0">
                                    <x-heroicon-m-chevron-up class="w-6 h-6" />
                                </button>

                                <button
                                    class="absolute top-[1.0rem] right-[2.5rem] text-primary-400 dark:text-primary-300"
                                    x-on:click="nextMatch" x-show="matchedMessages.length > 0">
                                    <x-heroicon-m-chevron-down class="w-6 h-6" />
                                </button>

                                <button class="absolute top-[0.6rem] right-3 text-primary-400 dark:text-primary-300">
                                    <x-heroicon-m-magnifying-glass class="w-6 h-6" />
                                </button>
                                <button class="absolute top-[0.6rem] right-[-1.70rem] text-gray-500 dark:text-gray-300"
                                    x-on:click="resetSearchState()">
                                    <x-heroicon-o-x-mark class="w-6 h-6" />
                                </button>
                            </div>
                            <!-- Error Message -->
                            <p x-show="searchError" class="text-danger-500 text-xs mt-2" x-text="searchError"></p>
                        </div>
                    </div>

                    <!-- Message Input Section -->
                    <div class="px-4 py-2 absolute bottom-0 left-0 w-full rounded-b-lg" :class="readOnlyPermission ? 'bg-white dark:bg-gray-900' :
                                'bg-transparent dark:bg-transparent'">

                        <!-- Conversation Limit Notice -->
                        <div x-show="conversationLimitReached" x-transition
                            class="mb-3 p-3 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-warning-400" />
                                </div>
                                <div class="ml-3 flex-1">
                                    <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                                        {{ t('conversation_limit_reached') }}
                                    </h3>
                                    <div class="mt-1 text-sm text-warning-700 dark:text-warning-300">
                                        <p x-text="limitErrorMessage"></p>
                                    </div>
                                    <div class="mt-3 flex space-x-3">
                                        <a href="{{ tenant_route('tenant.billing') ?? '#' }}"
                                            class="text-sm bg-warning-100 dark:bg-warning-800 text-warning-800 dark:text-warning-200 px-3 py-1 rounded-md hover:bg-warning-200 dark:hover:bg-warning-700 transition-colors">
                                            {{ t('upgrade_plan') }}
                                        </a>
                                        <button @click="refreshConversationLimit()"
                                            class="text-sm text-warning-700 dark:text-warning-300 hover:text-warning-900 dark:hover:text-warning-100">
                                            {{ t('try_again') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Error Messages Display -->
                        <div x-show="sendingErrorMessage" x-transition
                            class="mb-3 p-3 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-x-circle class="h-5 w-5 text-danger-400" />
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-danger-700 dark:text-danger-300"
                                        x-text="sendingErrorMessage"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Reply Preview -->
                        <template x-if="replyTo">
                            <div :class="{ 'min-h-[5rem]': !replyTo.text }"
                                class="p-3 mb-2 rounded-md flex border-primary-500 border-l-4 justify-between items-center z-60 bg-gray-100 dark:bg-gray-800">
                                <div class="flex items-start space-x-3 overflow-hidden">
                                    <!-- Image Preview -->
                                    <template x-if="replyTo.type === 'image'">
                                        <img :src="replyTo.url"
                                            class="w-[150px] h-[60px] object-cover rounded-md flex-shrink-0"
                                            alt="Image">
                                    </template>
                                    <!-- Video Preview -->
                                    <template x-if="replyTo.type === 'video'">
                                        <video :src="replyTo.url" controls
                                            class="w-[150px] h-[60px] object-cover rounded-md flex-shrink-0"></video>
                                    </template>
                                    <!-- Document Preview -->
                                    <template x-if="replyTo.type === 'document'">
                                        <a :href="replyTo.url" target="_blank"
                                            class="min-w-[60px] h-[40px] flex items-center justify-center bg-gray-200 dark:bg-gray-700 text-success-500 rounded-md px-2 text-xs font-medium truncate">
                                            {{ t('download_document') }}
                                        </a>
                                    </template>
                                    <!-- Audio Preview -->
                                    <template x-if="replyTo.type === 'audio'">
                                        <audio controls class="w-[200px] h-[30px]">
                                            <source :src="replyTo.url" type="audio/mpeg">
                                        </audio>
                                    </template>
                                    <!-- Text Reply -->
                                    <div class="text-gray-700 dark:text-gray-300 text-sm max-w-full">
                                        <span class="font-normal block truncate" x-text="replyTo.text"></span>
                                    </div>
                                </div>
                                <!-- Close Button -->
                                <button x-on:click="cancelReply"
                                    class="p-1 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition duration-200">
                                    <x-heroicon-o-x-mark class="w-4 h-4 text-gray-700 dark:text-gray-300" />
                                </button>
                            </div>
                        </template>

                        <div x-show="overdueAlert"
                            class="w-full bg-warning-100 dark:bg-warning-900 border border-warning-200 dark:border-warning-700 rounded-md p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm shadow-sm">
                            <div class="flex items-start gap-2 text-warning-800 dark:text-warning-100">
                                <!-- Heroicon: Exclamation Triangle -->
                                <x-heroicon-o-exclamation-triangle
                                    class="w-5 h-5 mt-0.5 flex-shrink-0 text-warning-600 dark:text-warning-200" />

                                <!-- Message -->
                                <div>
                                    <p class="font-semibold text-sm"> {{ t('24_hours_limit') }}</p>
                                    <p class="text-sm text-gray-700 dark:text-gray-200">
                                        {{ t('whatsapp_block_message_24_hours_after') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Button -->
                            <x-button.green x-show="getChatType !== 'guest'" x-on:click='handleModal()'>
                                <x-heroicon-o-chat-bubble-oval-left class="w-5 h-5 mr-1" />
                                {{ t('initiate_chat') }}
                            </x-button.green>
                        </div>


                        <div x-show="readOnlyPermission && !overdueAlert" class="w-full" x-cloak>
                            <div
                                class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 shadow">

                                <!-- Message Input Area -->
                                <div class="relative">

                                    <textarea :disabled="isRecording || conversationLimitReached" autocomplete="off"
                                        x-bind:class="{ 'opacity-50 cursor-not-allowed bg-gray-100': conversationLimitReached }"
                                        class="form-input mentionable w-full px-4 py-3 pr-16 text-gray-800 dark:text-gray-100 rounded-lg border-0 bg-transparent focus:outline-none focus:ring-0 focus:ring-primary-500 focus:border-transparent resize-none placeholder:text-sm placeholder:text-gray-500 dark:placeholder:text-gray-400"
                                        :placeholder="conversationLimitReached ? '{{ t('conversation_limit_reached') }}' : '{{ t('message_to') }} ' +
                                                (selectedUser?.name || 'user') +
                                                ' —> Shift + Enter for newline, use @ to mention'"
                                        id="textMessageInput" x-model="textMessage"
                                        @keydown.enter.prevent="handleEnterKey($event)"></textarea>

                                    <!-- Recording and Send buttons inside textarea (absolute positioned) -->
                                    <div class="absolute right-3 bottom-3 flex items-center space-x-1">
                                        <!-- Microphone Icon (Only Show When Input is Empty) -->
                                        <button type="button" x-show="!textMessage && !attachment && canSendMessage"
                                            class="bg-white dark:bg-gray-700 rounded-full p-1.5 text-primary-500 hover:text-primary-600 dark:hover:text-primary-400 dark:text-gray-400 shadow-sm border border-gray-200 dark:border-gray-600"
                                            x-on:click="toggleRecording()">
                                            <template x-if="isRecording">
                                                <x-heroicon-o-stop class="w-4 h-4 text-danger-500" />
                                            </template>
                                            <template x-if="!isRecording">
                                                <x-heroicon-o-microphone class="w-4 h-4"
                                                    data-tippy-content="{{ t('record_audio') }}" />
                                            </template>
                                        </button>

                                        <!-- Send Button (Only Show When Text is Entered or Recording) -->
                                        <button type="button"
                                            x-show="(textMessage || attachment || isRecording) && canSendMessage"
                                            class="bg-primary-100 dark:bg-gray-700 rounded-full p-1.5 text-primary-500 hover:text-primary-600 dark:hover:text-primary-400 dark:text-gray-400 shadow-sm border border-gray-200 dark:border-gray-600"
                                            x-on:click="sendMessage()"
                                            x-bind:disabled="sending || conversationLimitReached"
                                            x-bind:class="{ 'opacity-50 cursor-not-allowed': sending || conversationLimitReached }">
                                            <x-heroicon-o-paper-airplane class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Bottom Action Bar -->
                                <div
                                    class="flex items-center justify-between px-3 border-t border-gray-200 dark:border-gray-700">
                                    <!-- Left side action buttons -->
                                    <div class="flex items-center space-x-2">
                                        <!-- AI Icon (keeping your original logic) -->
                                        <div
                                            x-data="{ showAiButton: {{ get_tenant_setting_from_db('whats-mark', 'enable_openai_in_chat') ? 'true' : 'false' }} }">
                                            <button x-show="showAiButton" type="button"
                                                x-on:click="openAiMenu = !openAiMenu"
                                                :disabled="textMessage.trim() === ''"
                                                class="p-1.5 rounded hover:bg-primary-100 dark:hover:bg-gray-700 text-primary-600 dark:text-primary-400 disabled:cursor-not-allowed disabled:text-gray-300">
                                                <x-heroicon-o-cpu-chip class="w-5 h-5" />
                                            </button>
                                        </div>

                                        <!-- Emoji -->
                                        <button type="button" id="emoji_btn"
                                            x-on:click="showEmojiPicker = !showEmojiPicker; initializeEmojiPicker()"
                                            class="p-1.5 rounded hover:bg-primary-100 dark:hover:bg-gray-700 text-primary-600 dark:text-primary-400"
                                            data-tippy-content="{{ t('emojis') }}">
                                            <x-heroicon-o-face-smile class="w-5 h-5" />
                                        </button>


                                        <!-- Attachment -->
                                        <button type="button" x-on:click="showAttach = !showAttach"
                                            class="p-1.5 rounded hover:bg-primary-100 dark:hover:bg-gray-700 text-primary-600 dark:text-primary-400">
                                            <x-heroicon-o-paper-clip class="w-5 h-5"
                                                data-tippy-content="{{ t('attach_img_doc_vid') }}" />
                                        </button>


                                        {{-- Canned reply --}}
                                        <button x-show="cannedReplies.length > 0"
                                            x-on:click="showCannedReply = !showCannedReply; showAttach = false"
                                            class="p-1.5 rounded hover:bg-primary-100 dark:hover:bg-gray-700 text-primary-600 dark:text-primary-400 disabled:cursor-not-allowed disabled:text-gray-300"
                                            data-tippy-content="{{ t('canned_reply') }}">
                                            <x-heroicon-o-chat-bubble-left-ellipsis class="w-5 h-5" />

                                        </button>
                                    </div>

                                    <!-- Right side (optional status/info) -->
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        <!-- Optional: Character count, status, etc. -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Controls for Larger Screens -->
                        <div class="relative">
                            <!-- Dropdown Menu (Opens When Button is Clicked) -->
                            <div x-show="openAiMenu" x-on:click.away="openAiMenu = false" x-transition class="absolute bottom-14 left-0 w-[15rem] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                                shadow rounded-lg ">
                                <!-- AI Menu Items -->
                                <ul class="py-2 space-y-1">
                                    <!-- Change Tone -->
                                    <li x-data="{ changeToneSubmenu: false }" x-on:click="changeToneSubmenu = true"
                                        x-on:click.away="changeToneSubmenu = false"
                                        class="flex items-center justify-between px-4 py-2 rounded-md cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                        <div class="flex justify-start items-center">
                                            <x-heroicon-o-adjustments-horizontal class="w-5 h-5 mr-3 text-info-500" />
                                            <span class="text-gray-700 dark:text-gray-300 text-sm">{{ t('change_tone')
                                                }}
                                            </span>
                                        </div>
                                        <x-heroicon-m-chevron-right class="w-4 h-4 text-gray-700 dark:text-gray-300" />
                                        <div x-show="changeToneSubmenu" x-cloak class="absolute left-1/2 sm:left-full top-0 w-40 bg-white dark:bg-gray-800 border border-gray-200
                               dark:border-gray-700 shadow rounded-lg overflow-hidden z-50">
                                            <div x-show="loading"
                                                class="absolute z-[90] w-full h-full inset-0 items-center justify-center bg-white dark:bg-neutral-800 bg-opacity-70 ">
                                                <svg class="w-8 h-8 absolute top-[40%] right-[4rem] animate-spin text-primary-600"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <ul class="py-2">
                                                @foreach(\App\Enum\Tenant\WhatsAppTemplateRelationType::getAiChangeTone()
                                                as $key => $value)
                                                <li x-on:click="sendAiRequest('Change Tone', '{{ ucfirst($value) }}')"
                                                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 cursor-pointer text-sm">
                                                    {{ ucfirst($value) }}
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </li>

                                    <!-- Translate -->
                                    <li x-data="{
                                            options: @js($languages),
                                        }" x-on:click="showSubmenu = true" x-on:click.away="showSubmenu = false"
                                        class="relative flex items-center justify-between px-4 py-2 rounded-md cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                        <div class="flex justify-start items-center">
                                            <x-heroicon-o-language class="w-5 h-5 mr-3 text-success-500" />
                                            <span class="text-gray-700 dark:text-gray-300 text-sm">{{ t('translate')
                                                }}</span>
                                        </div>
                                        <x-heroicon-m-chevron-right class="w-4 h-4 text-gray-700 dark:text-gray-300" />
                                        <!-- Submenu for Countries with Fixed Height and Scrollbar -->
                                        <div x-show="showSubmenu" x-cloak
                                            class="absolute left-1/2 sm:left-full top-[-48px] w-48 bg-white dark:bg-gray-800 border border-gray-200
                                                     dark:border-gray-700 shadow-lg rounded-lg overflow-hidden max-h-[14rem] z-50">
                                            <!-- Search Bar -->
                                            <div class="p-2">
                                                <input type="text" placeholder="Search language..." x-model="search"
                                                    class="w-full px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700
                                                        border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-1
                                                        focus:ring-primary-500">
                                            </div>
                                            <div x-show="loading"
                                                class="absolute z-[90] w-full h-full inset-0 items-center justify-center bg-white dark:bg-neutral-800 bg-opacity-70">
                                                <svg class="w-8 h-8 absolute top-[45%] right-[5rem] animate-spin text-primary-600"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <ul class="py-2 max-h-44 overflow-y-auto">
                                                <template
                                                    x-for="language in options.filter(c => c.toLowerCase().includes(search.toLowerCase()))"
                                                    :key="language">
                                                    <li x-on:click="sendAiRequest('Translate', language)"
                                                        x-text="language.charAt(0).toUpperCase() + language.slice(1)"
                                                        class="p-2 border-b cursor-pointer hover:bg-gray-100">
                                                    </li>
                                                </template>

                                                <!-- No Results Message -->
                                                <li x-show="options.filter(c => c.toLowerCase().includes(search.toLowerCase())).length === 0"
                                                    class="p-2 text-gray-500 text-center">
                                                    {{ t('no_language_found') }}
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                    <div x-show="loading"
                                        class="absolute z-[90] w-full h-full inset-0 items-center justify-center bg-white dark:bg-neutral-800 bg-opacity-70">
                                        <svg class="w-8 h-8 absolute top-[40%] right-[6.4rem] animate-spin text-primary-600"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </div>
                                    <!-- Fix Spelling & Grammar -->
                                    <li x-on:click="sendAiRequest('Fix Spelling & Grammar', 'Fix Spelling & Grammar')"
                                        class="flex items-center px-4 py-2 rounded-md cursor-pointer
                               hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                        <x-heroicon-o-pencil class="w-5 h-5 mr-3 text-purple-500" />
                                        <span class="text-gray-700 dark:text-gray-300 text-sm">{{
                                            t('fix_spelling_and_grammar') }}</span>
                                    </li>

                                    <!-- Simplify Language -->
                                    <li x-on:click="sendAiRequest('Simplify Language', 'Simplify Language')" class="flex items-center px-4 py-2 rounded-md cursor-pointer
                               hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                        <x-heroicon-o-sparkles class="w-5 h-5 mr-3 text-warning-500" />
                                        <span class="text-gray-700 dark:text-gray-300 text-sm">{{ t('simplify_language')
                                            }}</span>
                                    </li>
                                    <!-- Custom Prompt -->
                                    <li x-data="{ showSubmenu: false }" x-on:click="showSubmenu = true"
                                        x-on:click.away="showSubmenu = false" class="relative flex items-center justify-between px-4 py-2 rounded-md cursor-pointer
                         hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                        <div class="flex justify-start items-center">
                                            <x-heroicon-o-light-bulb class="w-5 h-5 mr-3 text-danger-500" />
                                            <span class="text-gray-700 dark:text-gray-300 text-sm">{{ t('custom_prompt')
                                                }}
                                            </span>
                                        </div>
                                        <x-heroicon-m-chevron-right class="w-4 h-4 text-gray-700 dark:text-gray-300" />
                                        <!-- Submenu for AI Prompts -->

                                        <div x-show="showSubmenu" x-cloak class="absolute left-1/2 sm:left-full bottom-[-22%] w-48 bg-white dark:bg-gray-800 border border-gray-200
                                dark:border-gray-700 shadow rounded-lg overflow-hidden h-[10rem] overflow-y-auto">
                                            <div x-show="loading"
                                                class="absolute z-[90] w-full h-full inset-0 items-center justify-center bg-white dark:bg-neutral-800 bg-opacity-70">
                                                <svg class="w-8 h-8 absolute top-[40%] right-[5.4rem] animate-spin text-primary-600"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <ul class="py-2">
                                                @if (!empty($ai_prompt))
                                                @foreach ($ai_prompt as $prompt)
                                                <li x-on:click="sendAiRequest('Custom Prompt', {{ json_encode($prompt->action) }})"
                                                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 cursor-pointer text-sm">
                                                    {{ $prompt->name }}
                                                </li>
                                                @endforeach
                                                @else
                                                <li
                                                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 cursor-pointer text-sm">
                                                    {{ t('no_result_found') }}
                                                </li>
                                                @endif

                                            </ul>
                                        </div>

                                    </li>
                                </ul>
                            </div>
                            <!-- Canned Reply Card (Appears on Click) -->
                            <div x-show="showCannedReply" x-transition x-cloak x-on:click.away="showCannedReply = false"
                                class="absolute bottom-[4rem] left-6 w-[25rem] bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600
                                      rounded-md shadow p-4">

                                <!-- Title (Fixed) -->
                                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                    {{ t('canned_replies') }}
                                </h3>

                                <!-- Scrollable List -->
                                <ul class="space-y-3 max-h-48 overflow-y-auto">
                                    <template x-for="reply in filteredCannedReplies()" :key="reply.id">
                                        <li class="p-2 bg-gray-100 dark:bg-gray-700 rounded cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600"
                                            x-on:click="textMessage = reply.description, showCannedReply = false">

                                            <div class="flex items-center justify-between">
                                                <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm truncate"
                                                    x-text="reply.title"></p>

                                                <template x-if="reply.is_public">
                                                    <span
                                                        class="bg-primary-500 text-white text-xs font-medium px-2 py-1 rounded-lg">{{
                                                        t('Public') }}</span>
                                                </template>
                                            </div>

                                            <p class="text-gray-600 dark:text-gray-300 text-sm truncate"
                                                x-html="formatCannedReplies(reply.description)">
                                            </p>
                                        </li>
                                    </template>
                                </ul>

                            </div>

                            <!-- Dropdown (Appears above the button) -->
                            <div x-show="showAttach" x-transition x-on:click.away="showAttach = false"
                                class="absolute bottom-14 left-6 mb-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow p-2 z-40 w-40">
                                <button x-on:click="selectFileType('image')"
                                    class="flex items-center gap-2 w-full p-2 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <x-heroicon-o-photo class="w-5 h-5 text-primary-500" />
                                    <span> {{ t('image') }} </span>
                                </button>

                                <button x-on:click="selectFileType('document')"
                                    class="flex items-center gap-2 w-full p-2 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <x-heroicon-o-document class="w-5 h-5 text-success-500" />
                                    <span> {{ t('document') }} </span>
                                </button>

                                <button x-on:click="selectFileType('video')"
                                    class="flex items-center gap-2 w-full p-2 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <x-heroicon-o-video-camera class="w-5 h-5 text-danger-500" />
                                    <span> {{ t('video') }} </span>
                                </button>

                            </div>
                            <!-- Hidden File Inputs -->
                            <input type="file" id="image_upload" accept="image/*" class="hidden"
                                x-on:change="handleFilePreview($event, 'image')" />
                            <input type="file" id="document_upload" accept=".pdf,.doc,.docx,.txt" class="hidden"
                                x-on:change="handleFilePreview($event, 'document')" />
                            <input type="file" id="video_upload" accept="video/*" class="hidden"
                                x-on:change="handleFilePreview($event, 'video')" />
                        </div>
                        <!-- Emoji Picker -->
                        <div x-show="showEmojiPicker" id="emoji-picker-container"
                            x-on:click.outside="showEmojiPicker = false"
                            class="absolute bottom-[94%] left-[2px] sm:left-0 sm:bottom-full mb-2 z-50 rounded-md">
                            <div id="emoji-picker"></div>
                        </div>
                        <!-- Preview Section -->
                        <div x-show="previewUrl" class="absolute bottom-full rounded-md">
                            <div
                                class="bg-white dark:bg-gray-900 rounded-lg border border-gray-300 dark:border-gray-700 relative">
                                <!-- Close (X) Button at Top-Right -->
                                <button x-on:click="removePreview"
                                    class="absolute top-[-24px] right-[-2px] text-gray-600 dark:text-gray-300">
                                    <x-heroicon-o-x-mark class="w-6 h-6" />
                                </button>

                                <!-- Image Preview -->
                                <template x-if="previewType === 'image'">
                                    <img :src="previewUrl" class="w-full h-40 rounded-md object-cover" />
                                </template>

                                <!-- Document Preview -->
                                <template x-if="previewType === 'document'">
                                    <div class="p-4 bg-white dark:bg-gray-800 rounded-lg">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            <span class="font-semibold text-info-500" x-text="fileName"></span>
                                        </p>
                                    </div>
                                </template>

                                <!-- Video Preview -->
                                <template x-if="previewType === 'video'">
                                    <video controls class="w-full h-40 rounded-md">
                                        <source :src="previewUrl" type="video/mp4">
                                        {{ t('browser_not_support_video_tag') }}
                                    </video>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- Overlay User Information (Covers Chat Content) -->
                <div class="absolute inset-0 bg-black/60 z-40 hidden rounded-lg"
                    x-bind:class="{ '!block': isShowUserInfo }" x-on:click="isShowUserInfo = false">
                </div>
                <!-- User Information -->
                <div x-show="isShowUserInfo" x-cloak x-on:click.away="isShowUserInfo = false"
                    class="absolute top-0 right-0 w-96 h-[calc(100vh_-_100px)] bg-white dark:bg-gray-800 shadow-lg z-50 rounded transform transition-transform duration-300 overflow-hidden flex flex-col"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-x-full"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 transform translate-x-0"
                    x-transition:leave-end="opacity-0 transform translate-x-full">

                    <!-- Header -->
                    <div class="p-4 flex justify-between items-center border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">{{ t('user_info') }}
                        </h2>
                        <button x-on:click="isShowUserInfo = false"
                            class="text-gray-600 dark:text-gray-300 hover:text-danger-500">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    <!-- Scrollable Content -->
                    <div class="flex-1 overflow-y-auto p-4">
                        <!-- Profile Section -->
                        <div class="flex flex-col items-center text-center">
                            <div
                                class="rounded-full h-14 w-14 flex items-center justify-center bg-primary-100 text-primary-700 text-sm font-medium">
                                <span x-text="selectedUser?.name
                                ? selectedUser.name.split(' ').map(word => word[0]).join('').substring(0, 2).toUpperCase()
                                : 'U'" class="text-lg font-semibold">
                                </span>
                            </div>

                            <h3 class="text-lg font-medium text-gray-800 dark:text-white mt-2"
                                x-text="selectedUser?.name ?? 'Unknown'"></h3>
                            <span :class="selectedUser ? {
                                        'bg-violet-100 text-purple-800': selectedUser.type === 'lead',
                                        'bg-danger-100 text-danger-800': selectedUser.type === 'customer',
                                        'bg-warning-100 text-warning-800': selectedUser.type === 'guest',
                                        'bg-gray-100 text-gray-800': !['lead', 'customer', 'guest'].includes(
                                            selectedUser?.type)
                                    } : 'bg-gray-100 text-gray-800'"
                                class="inline-block ml-2 text-xs font-medium px-2 rounded">
                                <span x-text="selectedUser?.type ?? 'Unknown'"></span>
                            </span>
                        </div>

                        <!-- Details Section -->
                        <div class="border-t borde border-gray-200 dark:border-gray-700 p-2 mt-4">
                            <h4 class="text-md font-semibold text-gray-800 dark:text-white">{{ t('details') }}
                            </h4>
                        </div>

                        <div class="space-y-4 p-2">
                            <!-- Status Dropdown -->
                            <div class="flex items-center gap-3"
                                x-show="userInfo?.id && (selectedUser?.type === 'lead' || selectedUser?.type === 'customer')">
                                <x-heroicon-o-flag class="w-5 h-5 text-blue-500 dark:text-gray-400" />
                                <div class="flex items-center gap-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('status') }}</p>
                                    <div class="relative" x-data="{ isStatusDropdownOpen: false }">
                                        <button @click="isStatusDropdownOpen = !isStatusDropdownOpen"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                            :style="{ backgroundColor: userInfo?.status?.color + '20', borderColor: userInfo?.status?.color, color: userInfo?.status?.color }">
                                            <span x-text="userInfo?.status?.name || 'Select Status'"></span>
                                            <x-heroicon-m-chevron-down class="w-3 h-3 ml-1" />
                                        </button>

                                        <div x-show="isStatusDropdownOpen" @click.away="isStatusDropdownOpen = false"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute z-50 mt-1 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-600">
                                            @foreach($statuses as $status)
                                            <button
                                                @click="updateContactStatus({{ $status->id }}, '{{ $status->name }}', '{{ $status->color }}'); isStatusDropdownOpen = false"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 first:rounded-t-md last:rounded-b-md"
                                                :class="{ 'bg-gray-50 dark:bg-gray-700': userInfo?.status?.id === {{ $status->id }} }">
                                                <div class="w-3 h-3 rounded-full"
                                                    style="background-color: {{ $status->color }}"></div>
                                                <span>{{ $status->name }}</span>
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <x-heroicon-o-chat-bubble-left-ellipsis
                                    class="w-5 h-5 text-orange-500 dark:text-gray-400" />
                                <div class="flex items-center gap-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('source') }}</p>
                                    <div class="relative" x-data="{ isSourceDropdownOpen: false }"
                                        x-show="userInfo?.id && (selectedUser?.type === 'lead' || selectedUser?.type === 'customer')">
                                        <button @click="isSourceDropdownOpen = !isSourceDropdownOpen"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md border hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors bg-orange-50 border-orange-200 text-orange-700 dark:bg-orange-900/30 dark:border-orange-600 dark:text-orange-300">
                                            <span x-text="userInfo?.source?.name || 'Select Source'"></span>
                                            <x-heroicon-m-chevron-down class="w-3 h-3 ml-1" />
                                        </button>

                                        <div x-show="isSourceDropdownOpen" @click.away="isSourceDropdownOpen = false"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute z-50 mt-1 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-600">
                                            @foreach($sources as $source)
                                            <button
                                                @click="updateContactSource({{ $source->id }}, '{{ $source->name }}'); isSourceDropdownOpen = false"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 first:rounded-t-md last:rounded-b-md"
                                                :class="{ 'bg-gray-50 dark:bg-gray-700': userInfo?.source?.id === {{ $source->id }} }">
                                                <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                                                <span>{{ $source->name }}</span>
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <span
                                        x-show="!userInfo?.id || (selectedUser?.type !== 'lead' && selectedUser?.type !== 'customer')"
                                        class="text-primary-500 text-sm font-normal"
                                        x-text="userInfo?.source?.name ?? 'Unknown'"></span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <x-heroicon-o-user-group class="w-5 h-5 text-purple-500 dark:text-gray-400 mt-0.5" />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ t('groups') }}
                                        </p>

                                        <!-- Groups Dropdown -->
                                        <div x-data="{
                                        groupsOpen: false,
                                        selectedGroups: [],
                                        allGroups: {{ Js::from($groups) }},
                                        updateContactGroups() {
                                            if (!userInfo || !userInfo.id) return;

                                            // Get subdomain from parent Alpine component
                                            const subdomain = this.$root.subdomain || '{{ $subdomain }}';

                                            fetch(`/${subdomain}/update-contact-groups`, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                                                },
                                                body: JSON.stringify({
                                                    contact_id: userInfo.id,
                                                    group_ids: this.selectedGroups
                                                })
                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    userInfo.groups = data.groups;

                                                    // Show success notification
                                                    window.dispatchEvent(new CustomEvent('notify', {
                                                        detail: {
                                                            type: 'success',
                                                            message: data.message
                                                        }
                                                    }));
                                                } else {
                                                    // Show error notification
                                                    window.dispatchEvent(new CustomEvent('notify', {
                                                        detail: {
                                                            type: 'danger',
                                                            message: data.message
                                                        }
                                                    }));
                                                }
                                            })
                                            .catch(error => {
                                                // Show error notification
                                                window.dispatchEvent(new CustomEvent('notify', {
                                                    detail: {
                                                        type: 'danger',
                                                        message: 'Failed to update groups'
                                                    }
                                                }));
                                            });
                                        },
                                        toggleGroup(groupId) {
                                            const index = this.selectedGroups.indexOf(groupId);
                                            if (index > -1) {
                                                this.selectedGroups.splice(index, 1);
                                            } else {
                                                this.selectedGroups.push(groupId);
                                            }
                                            this.updateContactGroups();
                                        },
                                        isGroupSelected(groupId) {
                                            return this.selectedGroups.includes(groupId);
                                        }
                                    }" x-init="
                                        $watch('userInfo', (newVal) => {
                                            if (newVal && newVal.groups) {
                                                selectedGroups = newVal.groups.map(g => g.id);
                                            } else {
                                                selectedGroups = [];
                                            }
                                        });
                                    " class="relative">
                                            <!-- Dropdown Button -->
                                            <button @click="groupsOpen = !groupsOpen"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                                                <span
                                                    x-text="selectedGroups.length > 0 ? selectedGroups.length + ' group(s)' : 'Select groups'"></span>
                                                <x-heroicon-o-chevron-down class="w-3 h-3" />
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div x-show="groupsOpen" @click.away="groupsOpen = false"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95"
                                                class="absolute z-10 mt-1 w-48 bg-white rounded-md shadow-lg border border-gray-200 dark:bg-gray-700 dark:border-gray-600"
                                                style="display: none;">
                                                <div class="py-1 max-h-48 overflow-y-auto">
                                                    <template x-for="group in allGroups" :key="group.id">
                                                        <label
                                                            class="flex items-center px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                                                            <input type="checkbox" :checked="isGroupSelected(group.id)"
                                                                @change="toggleGroup(group.id)"
                                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                                                                x-text="group.name"></span>
                                                        </label>
                                                    </template>
                                                    <template x-if="allGroups.length === 0">
                                                        <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                                            No groups available
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Current Groups Display -->
                                    <div x-show="userInfo?.groups && userInfo.groups.length > 0"
                                        class="flex flex-wrap gap-1 mt-2">
                                        <template x-if="userInfo && userInfo.groups">
                                            <template x-for="group in userInfo.groups" :key="group.id">
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200"
                                                    x-text="group.name"></span>
                                            </template>
                                        </template>
                                    </div>
                                    <div x-data="{ userInfo: {}, selectedGroups: [] }">
                                        <span
                                            x-show="(!userInfo?.groups || userInfo.groups.length === 0) && selectedGroups.length === 0"
                                            class="text-xs text-gray-400 dark:text-gray-500">No groups assigned</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <x-heroicon-o-calendar class="w-5 h-5 text-sky-500 dark:text-gray-400" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ t('creation_time') }} <span class="text-primary-500 text-sm font-normal"
                                        x-show="userInfo?.created_at" x-text="new Date(userInfo?.created_at).toLocaleString('en-US', {
                                            year: 'numeric',
                                            month: 'short',
                                            day: '2-digit',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            second: '2-digit'
                                        })"></span>
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <x-heroicon-o-clock class="w-5 h-5 text-warning-500 dark:text-gray-400" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ t('last_activity') }} <span class="text-primary-500 text-sm font-medium"
                                        x-show="userInfo?.created_at" x-text="new Date(selectedUser?.time_sent).toLocaleString('en-US', {
                                            year: 'numeric',
                                            month: 'short',
                                            day: '2-digit',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            second: '2-digit'
                                        })"></span>
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <x-heroicon-o-phone class="w-5 h-5 text-success-500 dark:text-gray-400" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ t('phone') }} <span class="text-primary-500 text-sm font-medium"
                                        x-text="selectedUser?.receiver_id ? '+' + selectedUser.receiver_id : ''"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="border-t border-gray-200 dark:border-gray-700 p-2 mt-4">
                            <div class="flex justify-between items-center">
                                <h4 class="text-md font-semibold text-gray-800 dark:text-white">
                                    {{ t('notes_title') }}
                                </h4>
                                <button class="text-gray-600 dark:text-gray-300 hover:text-success-500"
                                    x-show="userInfo?.created_at">
                                    <a target="_blank" :href="`{{ tenant_route('tenant.contacts.save', ['contactId' => 'CONTACT_ID', 'notetab' => 'notes']) }}`
                                            .replace('CONTACT_ID', userInfo?.id || '')">
                                        <x-heroicon-o-plus class="w-5 h-5" />
                                    </a>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Simple Delete Confirmation Modal -->
            <div x-show="isDeleteChatModal" x-cloak>
                <div class="fixed inset-0 z-50">
                    <!-- Stylish Backdrop with Gradient -->
                    <div class="fixed inset-0 backdrop-blur-sm bg-gradient-to-br from-black/30 to-black/60">
                    </div>
                    <!-- Modal Container with Animation -->
                    <div class="fixed inset-0 z-50 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <div x-show="isDeleteChatModal" x-transition:enter="transition ease-out duration-300"
                                x-on:click.away="isDeleteChatModal = false"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="relative w-full max-w-lg overflow-hidden rounded-lg bg-white/95 dark:bg-slate-800/95 shadow-2xl ring-1 ring-black/5 dark:ring-white/5">
                                <!-- Gradient Background Accent -->

                                <div class=" px-4 pb-4 pt-5">
                                    <!-- Content Container -->
                                    <div class="sm:flex sm:items-start">
                                        <!-- Icon -->
                                        <div
                                            class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-danger-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-danger-600" />

                                        </div>
                                        <!-- Content -->
                                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                            <h3 class="text-base font-semibold leading-6 text-gray-900">
                                                {{ t('delete_chat_title') }}</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-slate-700">{{ t('delete_message') }} </p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Buttons -->
                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <button type="button" x-on:click="deleteChat(chatId)"
                                            class="inline-flex w-full justify-center rounded-md bg-danger-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-danger-500 sm:ml-3 sm:w-auto">
                                            {{ t('delete') }}</button>
                                        <button type="button" x-on:click="isDeleteChatModal = false"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                            {{ t('cancel') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div x-show="isInitiateChatModal" x-cloak>
                <div class="fixed inset-0 z-50">

                    <div class="fixed inset-0 z-50 overflow-y-auto">
                        <div class="flex justify-center p-4 mt-12">
                            <div x-data="{ modalSize: 'max-w-3xl', campaignsSelected: false, }"
                                x-show="isInitiateChatModal" x-transition:enter="transition ease-out duration-300"
                                x-effect="modalSize = campaignsSelected ? 'max-w-6xl' : 'max-w-2xl'"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" :class="modalSize"
                                class="relative w-full rounded-lg bg-white/95 dark:bg-slate-800/95 shadow-2xl ring-1 ring-black/5 dark:ring-white/5">

                                <div
                                    class="px-8 py-4 border-b border-neutral-200 dark:border-neutral-500/30 flex justify-between">
                                    <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                                        {{ t('initiate_chat') }}
                                    </h1>
                                    <button class="text-gray-500 hover:text-gray-700 text-2xl dark:hover:text-gray-300"
                                        x-on:click="modalClose()">
                                        &times;
                                    </button>
                                </div>

                                <div x-data="{

                                        fileError: null,
                                        isDisabled: false,
                                        campaignHeader: '',
                                        isSaving: false,
                                        campaignBody: '',
                                        campaignFooter: '',
                                        buttons: [],
                                        inputType: 'text',
                                        inputAccept: '',
                                        headerInputErrors: [],
                                        bodyInputErrors: [],
                                        footerInputErrors: [],
                                        headerParamsCount: 0,
                                        bodyParamsCount: 0,
                                        footerParamsCount: 0,
                                        selectedCount: 0,
                                        relType: '',
                                        // Added for preview
                                        previewType: '', // Store file type (image, video, document)
                                        previewFileName: '{{ !empty($filename) ? basename($filename) : '' }}',
                                        {{-- filteredContacts: @entangle('contacts'), --}}
                                        filteredContacts: '',
                                        metaExtensions: {{ json_encode(get_meta_allowed_extension()) }},
                                        {{-- isUploading: false, --}}
                                        progress: 0,

                                        uploadStarted() {
                                            this.isUploading = true;
                                            this.progress = 0;
                                            $dispatch('upload-started');
                                        },
                                        uploadFinished() {
                                            this.isUploading = false;
                                            this.progress = 100;
                                            $dispatch('upload-finished');
                                        },
                                        initTribute() {


                                            setTimeout(() => {
                                                if (typeof window.Tribute === 'undefined') {
                                                    return;
                                                }
                                                let tribute = new window.Tribute({
                                                    trigger: '@',
                                                    values: this.mergeFields,
                                                });
                                                document.querySelectorAll('.mentionable').forEach((el) => {
                                                    if (!el.hasAttribute('data-tribute')) {
                                                        tribute.attach(el);
                                                        el.setAttribute('data-tribute', 'true'); // Mark as initialized
                                                    }
                                                });
                                            }, 500);

                                        },
                                        handleCampaignChange(event) {

                                            this.selectedOption = event.target.selectedOptions[0];

                                            this.campaignsSelected = event.target.value !== '';
                                            this.campaignHeader = this.selectedOption?.dataset.header || '';
                                            this.campaignBody = this.selectedOption?.dataset.body || '';
                                            this.campaignFooter = this.selectedOption?.dataset.footer || '';
                                            this.buttons = this.selectedOption ? JSON.parse(this.selectedOption.dataset.buttons || '[]') : [];
                                            this.inputType = this.selectedOption?.dataset.headerFormat || 'text';
                                            this.headerParamsCount = parseInt(this.selectedOption?.dataset.headerParamsCount || 0);
                                            this.bodyParamsCount = parseInt(this.selectedOption?.dataset.bodyParamsCount || 0);
                                            this.footerParamsCount = parseInt(this.selectedOption?.dataset.footerParamsCount || 0);
                                            this.editTemplateId = this.selectedOption.value;

                                            this.hInput = Array(this.headerParamsCount).fill('');
                                            this.bInput = Array(this.bodyParamsCount).fill('');
                                            this.footerInputs = Array(this.footerParamsCount).fill('');

                                            if (!this.selectedOption || !this.previewUrl.includes('{{ $filename ?? '' }}')) {
                                                this.previewUrl = '';
                                                this.previewFileName = '';
                                            }

                                            const format = this.selectedOption?.dataset.headerFormat || 'text';
                                            this.inputAccept = this.metaExtensions[format.toLowerCase()]?.extension || '';

                                        },

                                        replaceVariables(template, inputs) {
                                            if (!template || !inputs) return ''; // Prevent undefined error
                                            return template.replace(/\{\{(\d+)\}\}/g, (match, p1) => {
                                                const index = parseInt(p1, 10) - 1;
                                                return `<span class='text-primary-600'>${inputs[index] || match}</span>`;
                                            });
                                        },
                                        handleFilePreview(event) {

                                            const file = event.target.files[0];
                                            this.fileError = null; // Clear previous errors

                                            if (!file) {
                                                return;
                                            }

                                            // Get allowed extensions and max size from metaExtensions
                                            const typeKey = this.inputType.toLowerCase(); // Convert to lowercase for consistency
                                            const metaData = this.metaExtensions[typeKey];


                                            const allowedExtensions = metaData.extension.split(',').map(ext => ext.trim());
                                            const maxSizeMB = metaData.size || 0; // Default to 0 if not set
                                            const maxSizeBytes = maxSizeMB * 1024 * 1024; // Convert MB to bytes

                                            // Extract file extension
                                            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

                                            // Validate file extension (from metaExtensions)
                                            if (!allowedExtensions.includes(fileExtension)) {
                                                this.fileError = `Invalid file type. Allowed types: ${allowedExtensions.join(', ')}`;
                                                return;
                                            }

                                            // MIME type validation (strict check)
                                            const fileType = file.type.split('/')[0];

                                            if (this.inputType === 'DOCUMENT' && !['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'text/plain'].includes(file.type)) {
                                                this.fileError = 'Invalid document type. Please upload a valid document.';
                                                return;
                                            }

                                            if (this.inputType === 'IMAGE' && !file.type.startsWith('image/')) {
                                                this.fileError = 'Invalid image file. Please upload an image.';
                                                return;
                                            }

                                            if (this.inputType === 'VIDEO' && !file.type.startsWith('video/')) {
                                                this.fileError = 'Invalid video file. Please upload a video.';
                                                return;
                                            }

                                            if (this.inputType === 'AUDIO' && !file.type.startsWith('audio/')) {
                                                this.fileError = 'Invalid audio file. Please upload an audio file.';
                                                return;
                                            }

                                            if (this.inputType === 'STICKER' && file.type !== 'image/webp') {
                                                this.fileError = 'Invalid sticker file. Only .webp format is allowed.';
                                                return;
                                            }

                                            // Validate file size
                                            if (file.size > maxSizeBytes) {
                                                this.fileError = `File size exceeds ${maxSizeMB} MB. Please upload a smaller file.`;
                                                return;
                                            }

                                            // If validation passes, handle the file preview
                                            this.prev = URL.createObjectURL(file);
                                            this.previewUrl = this.prev;
                                            this.previewFileName = file.name;
                                            this.fileInput = file.name;
                                            this.fileI = file;


                                        },
                                        validateInputs() {
                                            const hasTextInputs = this.headerParamsCount > 0 || this.bodyParamsCount > 0 || this.footerInputs.length > 0;
                                            const hasFileInput = ['IMAGE', 'VIDEO', 'DOCUMENT', 'AUDIO'].includes(this.inputType);

                                            if (!hasTextInputs && !hasFileInput) {
                                                return true;
                                            }
                                            const validateInputGroup = (inputs, paramsCount) => {
                                                // Ensure inputs is a properly unwrapped array
                                                const unwrappedInputs = inputs ? JSON.parse(JSON.stringify(inputs)) : [];

                                                // Ensure length matches paramsCount by filling missing values with empty strings
                                                while (unwrappedInputs.length < paramsCount) {

                                                    unwrappedInputs.push('');
                                                }

                                                // Return errors if inputs are empty
                                                return unwrappedInputs.map(value =>
                                                    value.trim() === '' ? '{{ t('this_field_is_required') }}' : ''
                                                );
                                            };

                                            // Validate text inputs
                                            this.headerInputErrors = validateInputGroup(this.hInput, this.headerParamsCount);
                                            this.bodyInputErrors = validateInputGroup(this.bInput, this.bodyParamsCount);
                                            this.footerInputErrors = validateInputGroup(this.footerInputs, this.footerInputs.length);

                                            if (hasFileInput && !this.previewFileName) {
                                                this.fileError = '{{ t('this_field_is_required') }}';
                                            } else {
                                                this.fileError = ''; // Reset file error if not needed
                                            }

                                            // Check if all inputs are valid
                                            const isTextValid = [this.headerInputErrors, this.bodyInputErrors, this.footerInputErrors]
                                                .every(errors => errors.length === 0 || errors.every(error => error === ''));


                                            const isFileValid = !this.fileError; // No error means file validation passed

                                            return isTextValid && isFileValid;
                                        },

                                        handleSave() {

                                            if (!this.validateInputs()) {
                                                return; // Prevent further action if validation fails
                                            }

                                            submitChat(selectedUser.id)

                                        }

                                    }" x-init="$nextTick(() => {
                                        const select = $el.querySelector('#basic-select');

                                        if (select?.value) {
                                            handleCampaignChange({ target: select });
                                        }
                                        if (isInitiateChatModal = false) {

                                            this.campaignsSelected = '';
                                        }
                                    })" class="">

                                    <div class="px-6 py-4">
                                        <form @submit.prevent="handleSave" enctype="multipart/form-data">
                                            @csrf

                                            {{-- template_name --}}
                                            <div class="mt-1 mb-2">
                                                <div class="flex item-centar justify-start">
                                                    <span class="text-danger-500 me-1 ">*</span>
                                                    <x-label for="template_id" :value="t('template')" />
                                                </div>

                                                <div wire:ignore x-cloak>
                                                    <x-select id="basic-select" class="block w-full tom-select"
                                                        wire:model.defer="template_id" x-ref="campaignsChange"
                                                        x-on:change="handleCampaignChange({ target: $refs.campaignsChange });"
                                                        x-init="() => {
                                                                handleCampaignChange({ target: $refs.campaignsChange });
                                                            }">
                                                        <option value="" selected>
                                                            {{ t('nothing_selected') }}
                                                        </option>

                                                        @foreach ($templates as $template)
                                                        <option value="{{ $template['template_id'] }}"
                                                            data-header="{{ $template['header_data_text'] }}"
                                                            data-body="{{ $template['body_data'] }}"
                                                            data-footer="{{ $template['footer_data'] }}"
                                                            data-buttons="{{ $template['buttons_data'] }}"
                                                            data-header-format="{{ $template['header_data_format'] }}"
                                                            data-header-params-count="{{ $template['header_params_count'] }}"
                                                            data-body-params-count="{{ $template['body_params_count'] }}"
                                                            data-footer-params-count="{{ $template['footer_params_count'] }}">
                                                            {{ $template['template_name'] . ' (' . $template['language']
                                                            . ')' }}
                                                        </option>
                                                        @endforeach

                                                    </x-select>
                                                </div>

                                                <x-input-error for="template_id" class="mt-2" />
                                            </div>
                                            <div x-show="campaignsSelected" x-cloak
                                                class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    {{-- Variables --}}
                                                    <x-card class="rounded-lg mt-8">
                                                        <x-slot:header>
                                                            <h1
                                                                class="text-xl font-semibold text-slate-700 dark:text-slate-300 ">
                                                                {{ t('variables') }}
                                                            </h1>
                                                        </x-slot:header>
                                                        <x-slot:content>
                                                            <div>
                                                                <!-- Alert for missing variables -->
                                                                <div x-show="((inputType == 'TEXT' || inputType == '') && headerParamsCount === 0) && bodyParamsCount === 0 && footerParamsCount === 0"
                                                                    class="bg-danger-100 border-l-4 rounded border-danger-500 text-danger-800 px-2 py-3 dark:bg-gray-800 dark:border-danger-800 dark:text-danger-300"
                                                                    role="alert">
                                                                    <div class="flex justify-start items-center gap-2">
                                                                        <p class="font-base text-sm">
                                                                            {{
                                                                            t('variable_not_available_for_this_template')
                                                                            }}
                                                                        </p>
                                                                    </div>
                                                                </div>

                                                                {{-- Header section --}}
                                                                <div
                                                                    x-show="inputType !== 'TEXT' || headerParamsCount > 0">
                                                                    <div class="flex items-center justify-start">
                                                                        <label for="dynamic_input"
                                                                            class="block font-medium text-slate-700 dark:text-slate-200">
                                                                            <template
                                                                                x-if="inputType == 'TEXT' && headerParamsCount > 0">
                                                                                <span class="text-lg font-semibold">{{
                                                                                    t('header') }}</span>
                                                                            </template>
                                                                            <template x-if="inputType == 'IMAGE'">
                                                                                <span class="text-lg font-semibold">{{
                                                                                    t('image') }}</span>
                                                                            </template>
                                                                            <template x-if="inputType == 'DOCUMENT'">
                                                                                <span class="text-lg font-semibold">{{
                                                                                    t('document') }}</span>
                                                                            </template>
                                                                            <template x-if="inputType == 'VIDEO'">
                                                                                <span class="text-lg font-semibold">{{
                                                                                    t('video') }}</span>
                                                                            </template>
                                                                        </label>
                                                                    </div>

                                                                    <div>
                                                                        <!-- Standard Input with Tailwind CSS -->
                                                                        <template x-if="inputType == 'TEXT'">
                                                                            <template
                                                                                x-for="(value, index) in headerParamsCount"
                                                                                :key="index">
                                                                                <div class="mt-2">
                                                                                    <div
                                                                                        class="flex justify-start gap-1">
                                                                                        <span
                                                                                            class="text-danger-500">*</span>
                                                                                        <label
                                                                                            :for="'header_name_' + index"
                                                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                                            {{ t('variable') }}
                                                                                            <span
                                                                                                x-text="index + 1"></span>
                                                                                        </label>
                                                                                    </div>
                                                                                    <input x-bind:type="inputType"
                                                                                        :id="'header_name_' + index"
                                                                                        x-model="hInput[index]"
                                                                                        x-init="initTribute()"
                                                                                        class="mentionable block mt-1 w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600"
                                                                                        autocomplete="off" />
                                                                                    <p x-show="headerInputErrors[index]"
                                                                                        x-text="headerInputErrors[index]"
                                                                                        class="text-danger-500 text-sm mt-1">
                                                                                    </p>
                                                                                </div>
                                                                            </template>
                                                                        </template>
                                                                        @if ($errors->has('hInput.*'))
                                                                        <x-dynamic-alert type="danger" :message="$errors->first(
                                                                                        'hInput.*',
                                                                                    )" class="mt-4"></x-dynamic-alert>
                                                                        @endif
                                                                        <!-- For DOCUMENT input type (file upload) -->
                                                                        <template x-if="inputType == 'DOCUMENT'">
                                                                            <div>
                                                                                <label for="document_upload"
                                                                                    class="block text-sm font-medium text-gray-800 dark:text-gray-300">
                                                                                    {{ t('select_document') }}
                                                                                    <span
                                                                                        x-text="metaExtensions.document.extension"></span>
                                                                                </label>

                                                                                <div class="relative mt-1 p-6 border-2 border-dashed rounded-lg cursor-pointer hover:border-info-500 transition duration-300"
                                                                                    x-on:click="$refs.documentUpload.click()">
                                                                                    <div class="text-center">
                                                                                        <x-heroicon-s-photo
                                                                                            class="h-12 w-12 text-gray-400 mx-auto" />
                                                                                        <p
                                                                                            class="mt-2 text-sm text-gray-600">
                                                                                            {{ t('select_or_browse_to')
                                                                                            }}
                                                                                            <span
                                                                                                class="text-info-600 underline">{{
                                                                                                t('document') }}</span>
                                                                                        </p>
                                                                                    </div>
                                                                                    <input type="file"
                                                                                        x-ref="documentUpload"
                                                                                        id="document_upload"
                                                                                        x-bind:accept="inputAccept"
                                                                                        wire:model="file"
                                                                                        x-on:change="handleFilePreview($event)"
                                                                                        class="hidden" />
                                                                                </div>
                                                                                <template x-if="fileError">
                                                                                    <p class="text-danger-500 text-sm mt-2"
                                                                                        x-text="fileError">
                                                                                    </p>
                                                                                </template>
                                                                            </div>
                                                                        </template>

                                                                        <!-- For IMAGE input type (image file upload) -->
                                                                        <template x-if="inputType === 'IMAGE'">
                                                                            <div>
                                                                                <label for="image_upload"
                                                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                                    {{ t('select_image') }}
                                                                                    <span
                                                                                        x-text="metaExtensions.image.extension"></span>
                                                                                </label>
                                                                                <div class="relative mt-1 p-6 border-2 border-dashed rounded-lg cursor-pointer hover:border-info-500 transition duration-300"
                                                                                    x-on:click="$refs.imageUpload.click()">
                                                                                    <div class="text-center">
                                                                                        <x-heroicon-s-photo
                                                                                            class="h-12 w-12 text-gray-400 mx-auto" />
                                                                                        <p
                                                                                            class="mt-2 text-sm text-gray-600">
                                                                                            {{ t('select_or_browse_to')
                                                                                            }}
                                                                                            <span
                                                                                                class="text-info-600 underline">{{
                                                                                                t('image') }}</span>
                                                                                        </p>
                                                                                    </div>
                                                                                    <input type="file" id="image_upload"
                                                                                        x-ref="imageUpload"
                                                                                        x-bind:accept="inputAccept"
                                                                                        wire:model.defer="file"
                                                                                        x-on:change="handleFilePreview($event)"
                                                                                        class="hidden" />
                                                                                </div>

                                                                                @if ($errors->has('file'))
                                                                                <x-input-error class="mt-2"
                                                                                    for="file" />
                                                                                @else
                                                                                <template x-if="fileError">
                                                                                    <p class="text-danger-500 text-sm mt-2"
                                                                                        x-text="fileError">
                                                                                    </p>
                                                                                </template>
                                                                                @endif
                                                                            </div>
                                                                        </template>

                                                                        <!-- For VIDEO input type (video file upload) -->
                                                                        <template x-if="inputType == 'VIDEO'">
                                                                            <div>
                                                                                <label for="video_upload"
                                                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                                    {{ t('select_video') }}
                                                                                </label>
                                                                                <span
                                                                                    x-text="metaExtensions.video.extension"></span>
                                                                                <div class="relative mt-1 p-6 border-2 border-dashed rounded-lg cursor-pointer hover:border-info-500 transition duration-300"
                                                                                    x-on:click="$refs.videoUpload.click()">
                                                                                    <div class="text-center">
                                                                                        <x-heroicon-s-photo
                                                                                            class="h-12 w-12 text-gray-400 mx-auto" />
                                                                                        <p
                                                                                            class="mt-2 text-sm text-gray-600">
                                                                                            {{ t('select_or_browse_to')
                                                                                            }}
                                                                                            <span
                                                                                                class="text-info-600 underline">{{
                                                                                                t('video') }}</span>
                                                                                        </p>
                                                                                    </div>
                                                                                    <input type="file" id="video_upload"
                                                                                        x-ref="videoUpload"
                                                                                        x-bind:accept="inputAccept"
                                                                                        wire:model.defer="file"
                                                                                        x-on:change="handleFilePreview($event)"
                                                                                        class="hidden" />
                                                                                </div>
                                                                                <template x-if="fileError">
                                                                                    <p class="text-danger-500 text-sm mt-2"
                                                                                        x-text="fileError">
                                                                                    </p>
                                                                                </template>
                                                                            </div>
                                                                        </template>

                                                                    </div>
                                                                </div>
                                                                {{-- Body section --}}
                                                                <div x-show="bodyParamsCount > 0">
                                                                    <div class="flex items-center justify-start mt-2">
                                                                        <label for="dynamic_input"
                                                                            class="block font-medium text-slate-700 dark:text-slate-200">
                                                                            <span class="text-lg font-semibold">{{
                                                                                t('body') }}</span>
                                                                        </label>
                                                                    </div>

                                                                    <div>
                                                                        <template
                                                                            x-for="(value, index) in bodyParamsCount"
                                                                            :key="index">
                                                                            <div class="mt-2">
                                                                                <div class="flex justify-start gap-1">
                                                                                    <span
                                                                                        class="text-danger-500">*</span>
                                                                                    <label :for="'body_name_' + index"
                                                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                                        {{ t('variable') }} <span
                                                                                            x-text="index + 1"></span>
                                                                                    </label>
                                                                                </div>
                                                                                <input type="text"
                                                                                    :id="'body_name_' + index"
                                                                                    x-model="bInput[index]"
                                                                                    x-init='initTribute()'
                                                                                    class="mentionable block mt-1 w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600"
                                                                                    autocomplete="off" />
                                                                                <p x-show="bodyInputErrors[index]"
                                                                                    x-text="bodyInputErrors[index]"
                                                                                    class="text-danger-500 text-sm mt-1">
                                                                                </p>
                                                                            </div>
                                                                        </template>
                                                                        @if ($errors->has('bInput.*'))
                                                                        <x-dynamic-alert type="danger" :message="$errors->first(
                                                                                        'bInput.*',
                                                                                    )" class="mt-4"></x-dynamic-alert>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                {{-- Footer section --}}
                                                                <div x-show="footerParamsCount > 0">
                                                                    <div
                                                                        class="text-gray-600 dark:text-gray-400 border-b mt-8 mb-6 border-gray-300 dark:border-gray-600">
                                                                    </div>

                                                                    {{-- Footer section --}}
                                                                    <div class="flex items-center justify-start">
                                                                        <label for="dynamic_input"
                                                                            class="block font-medium text-slate-700 dark:text-slate-200">
                                                                            <span class="text-lg font-semibold">{{
                                                                                t('footer') }}</span>
                                                                        </label>
                                                                    </div>

                                                                    <div>
                                                                        <template x-for="(value, index) in footerInputs"
                                                                            :key="index">
                                                                            <div class="mt-2">
                                                                                <div class="flex justify-start gap-1">
                                                                                    <span
                                                                                        class="text-danger-500">*</span>
                                                                                    <label :for="'footer_name_' + index"
                                                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                                                        {{ t('variable') }} <span
                                                                                            x-text="index"></span>
                                                                                    </label>
                                                                                </div>
                                                                                <input type="text"
                                                                                    :id="'footer_name_' + index"
                                                                                    x-model="footerInputs[index]"
                                                                                    class="mentionable block mt-1 w-full border-slate-300 rounded-md shadow-sm text-slate-900 sm:text-sm focus:ring-info-500 focus:border-info-500 disabled:opacity-50 dark:border-slate-500 dark:bg-slate-800 dark:placeholder-slate-500 dark:text-slate-200 dark:focus:ring-info-500 dark:focus:border-info-500 dark:focus:placeholder-slate-600"
                                                                                    autocomplete="off" />
                                                                                <p x-show="footerInputErrors[index]"
                                                                                    x-text="footerInputErrors[index]"
                                                                                    class="text-danger-500 text-sm mt-1">
                                                                                </p>
                                                                            </div>
                                                                        </template>
                                                                        @if ($errors->has('footerInputs.*'))
                                                                        <x-dynamic-alert type="danger" :message="$errors->first(
                                                                                        'footerInputs.*',
                                                                                    )" class="mt-4"></x-dynamic-alert>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </x-slot:content>
                                                    </x-card>
                                                </div>
                                                <div class="h-full">
                                                    {{-- Preview --}}
                                                    <x-card class="rounded-lg mt-8">
                                                        <x-slot:header>
                                                            <h1
                                                                class="text-xl font-semibold text-slate-700 dark:text-slate-300 ">
                                                                {{ t('preview') }}
                                                            </h1>
                                                        </x-slot:header>
                                                        <x-slot:content>
                                                            <div class="w-full p-6 border border-gray-200 rounded shadow-sm dark:border-gray-700"
                                                                style="background-image: url('{{ asset('img/chat/whatsapp_light_bg.png') }}');">
                                                                <!-- File Preview Section -->
                                                                <div class="mb-1" x-show="previewUrl">
                                                                    <!-- Image Preview -->
                                                                    <a x-show="inputType === 'IMAGE'"
                                                                        x-init="$nextTick(() => { window.initGLightbox() })"
                                                                        :href="previewUrl" class="glightbox">
                                                                        <img x-show="inputType === 'IMAGE'"
                                                                            :src="previewUrl"
                                                                            class="w-full max-h-60 rounded-lg shadow bg-white dark:bg-gray-800" />
                                                                    </a>

                                                                    <!-- Video Preview -->
                                                                    <video x-show="inputType === 'VIDEO'"
                                                                        x-init="$nextTick(() => { window.initGLightbox() })"
                                                                        :src="previewUrl" controls
                                                                        class="w-full max-h-60 rounded-lg shadow bg-white dark:bg-gray-800 glightbox cursor-pointer"></video>

                                                                    <!-- Document Preview -->
                                                                    <div x-show="inputType === 'DOCUMENT'"
                                                                        class="p-4 border border-gray-300 bg-white dark:bg-gray-800 rounded-lg">
                                                                        <p
                                                                            class="text-sm text-gray-500 dark:text-gray-400">
                                                                            {{ t('document_uploaded') }}
                                                                            <a :href="previewUrl" target="_blank"
                                                                                class="text-info-500 underline break-all inline-block"
                                                                                x-text="previewFileName"></a>
                                                                        </p>
                                                                    </div>
                                                                </div>

                                                                <!-- Campaign Text Section -->
                                                                <div
                                                                    class="p-6 bg-white rounded-lg dark:bg-gray-800 dark:text-white">
                                                                    <p class="mb-3 font-meduim text-gray-800 dark:text-gray-400"
                                                                        x-html="replaceVariables(campaignHeader, hInput)">
                                                                    </p>
                                                                    <p class="mb-3 font-normal text-sm text-gray-500 dark:text-gray-400"
                                                                        x-html="replaceVariables(campaignBody, bInput)">
                                                                    </p>
                                                                    <div class="mt-4">
                                                                        <p class="font-normal text-xs text-gray-500 dark:text-gray-400"
                                                                            x-text="campaignFooter">
                                                                        </p>
                                                                    </div>
                                                                </div>

                                                                <template x-if="buttons && buttons.length > 0"
                                                                    class="bg-white rounded-lg py-2 dark:bg-gray-800 dark:text-white">
                                                                    <!-- Check if buttons is defined and not empty -->
                                                                    <div class="space-y-1">
                                                                        <!-- Use space-y-2 for vertical spacing between buttons -->
                                                                        <template x-for="(button, index) in buttons"
                                                                            :key="index">
                                                                            <div
                                                                                class="w-full px-4 py-2 bg-white text-gray-900 rounded-md dark:bg-gray-700 dark:text-white">
                                                                                <span x-text="button.text"
                                                                                    class="text-sm block text-center"></span>
                                                                                <!-- Center the text inside the button -->
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </x-slot:content>

                                                    </x-card>

                                                </div>
                                            </div>
                                            <div x-show="campaignsSelected" x-cloak
                                                class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30  mt-5 px-6">
                                                <x-button.secondary x-on:click="modalClose()">
                                                    {{ t('cancel') }}
                                                </x-button.secondary>

                                                <x-button.loading-button type="button" x-on:click="
                            initiate_chat_loading =true;
                            handleSave();
                          " x-bind:disabled="initiate_chat_loading"
                                                    x-bind:class="{ 'opacity-50 cursor-not-allowed': initiate_chat_loading }">
                                                    <span x-show="initiate_chat_loading">
                                                        <x-heroicon-o-arrow-path class="animate-spin w-4 h-4 my-1" />
                                                    </span>
                                                    <span x-show="!initiate_chat_loading">
                                                        {{ t('submit') }}
                                                    </span>
                                                </x-button.loading-button>

                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div x-show="isSupportAgentModal" x-cloak>
                <div class="fixed inset-0 z-50">
                    <!-- Stylish Backdrop with Gradient -->
                    <div class="fixed inset-0 backdrop-blur-sm bg-gradient-to-br from-black/30 to-black/60">
                    </div>
                    <!-- Modal Container with Animation -->
                    <div class="fixed inset-0 z-50 overflow-y-auto">
                        <div class="flex min-h-[50%] items-center justify-center p-4">
                            <div x-show="isSupportAgentModal" x-transition:enter="transition ease-out duration-300"
                                x-on:click.away="closeSupportAgentModal()" x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="relative w-full max-w-xl  rounded-lg bg-white/95 dark:bg-slate-800/95 shadow-2xl ring-1 ring-black/5 dark:ring-white/5">
                                <!-- Gradient Background Accent -->

                                <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-500/30">
                                    <h1 class="text-xl font-medium text-slate-800 dark:text-slate-300">
                                        {{ t('support_agent') }}
                                    </h1>
                                </div>
                                <div class=" mx-auto mt-3 px-4" x-init="$watch('selectedOptions', value => {})">
                                    <div class="relative">

                                        <!-- Hidden Input for Livewire -->
                                        <input type="hidden" id="support_agent" name="selectedAgent"
                                            wire:model="selectedAgent"
                                            :value="selectedOptions.map(o => o.id).join(',')">

                                        <div class="mt-1 relative">
                                            <!-- Dropdown Button -->
                                            <button type="button" x-on:click="open = !open"
                                                class="relative w-full cursor-default rounded-md border border-slate-300 bg-white py-2 pl-3 pr-10 text-left shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:focus:ring-offset-slate-800">
                                                <span class="block truncate"
                                                    x-text="selectedOptions.length ? selectedOptions.map(o => o.firstname).join(', ') : 'Select Users'">
                                                </span>
                                                <span
                                                    class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400"
                                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                        fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd"
                                                            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div x-show="open" x-on:click.away="open = false"
                                                class="absolute z-10 mt-1 w-full bg-white dark:bg-slate-700 dark:text-white shadow-lg max-h-60 rounded-md ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                                style="display: none;">

                                                <!-- Search Bar -->
                                                <div class="p-2">
                                                    <input type="text" x-model="search" placeholder="Search users..."
                                                        class="w-full border border-gray-300 rounded-md p-2 dark:bg-slate-800 dark:placeholder:text-white focus:ring-primary-500 focus:border-primary-500">
                                                </div>

                                                <!-- User List -->
                                                <ul>
                                                    <template x-if="filteredOptions.length">
                                                        <template x-for="option in filteredOptions" :key="option.id">
                                                            <li x-on:click="toggleOption(option)"
                                                                class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-primary-400 dark:hover:bg-gray-800 hover:text-white flex items-center">

                                                                <!-- User Name -->
                                                                <span x-text="option.firstname + ' ' + option.lastname"
                                                                    :class="{
                                                                            'font-semibold': selectedOptions.some(
                                                                                o => o
                                                                                .id ===
                                                                                option.id)
                                                                        }" class="block truncate"></span>

                                                                <!-- Checkmark Icon -->
                                                                <span
                                                                    x-show="selectedOptions.some(o => o.id === option.id)"
                                                                    class="absolute right-4 text-primary-600 dark:text-primary-400">
                                                                    <x-heroicon-s-check class="h-5 w-5" />
                                                                </span>
                                                            </li>
                                                        </template>
                                                    </template>

                                                    <template x-if="filteredOptions.length === 0">
                                                        <li class="p-2 text-gray-500 dark:text-white text-center">
                                                            {{ t('no_result_found') }}
                                                        </li>
                                                    </template>

                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="py-4 flex justify-end space-x-3 border-t border-neutral-200 dark:border-neutral-500/30 mt-5 px-6">
                                    <x-button.secondary x-on:click="closeSupportAgentModal()">
                                        {{ t('cancel') }}
                                    </x-button.secondary>
                                    <x-button.loading-button type="submit" x-on:click="submitAgent(selectedUser.id)">
                                        {{ t('submit') }}
                                    </x-button.loading-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
<script src="{{ asset('location/leaflet.js') }}" crossorigin=""></script>
<script>
    function chatApp(chatData) {
        return {
            chats: chatData,
            sortedChats: [], // New array for sorted chats
            selectedUser: {},
            replyTo: null,
            isShowChatMenu: false,
            isShowUserChat: false,
            isShowUserInfo: false,
            showAttach: false,
            textMessage: '',
            previewUrl: '',
            previewType: '',
            fileName: '',
            attachment: null,
            attachmentType: '',
            searchText: '',
            searchMessagesText: '',
            matchedMessages: '',
            searchError: '',
            sendingErrorMessage: '',
            messagesSearch: false,
            showReactionList: false,
            activeMessageId: null,
            showEmojiPicker: false,
            isRecording: false,
            isRecording: false,
            audioBlob: null,
            recordedAudio: null,
            readOnlyPermission: {{ $readOnlyPermission }},
            selectedWaNo: '',
            filteredChat: [],
            overdueAlert: false,
            remainingHours: 0,
            remainingMinutes: 0,
            showAlert: false,
            openAiMenu: false,
            showCannedReply: false,
            loading: false,
            hideUnreadCount: false,
            hasUserInteracted: false,
            userInfo: [],
            cannedReplies: @json($canned_reply),
            subdomain: @json($subdomain),
            mergeFields: [],
            messages: '',
            sources: @json($sources),
            statuses: @json($statuses),
            groups: @json($groups),
            assigneeprofile: [],
            chatId: '',
            metaExtensions: @json(get_meta_allowed_extension()),
            usersname: [],
            users: {!! json_encode($users) !!},
            currentUserId: {!! json_encode(auth()->id()) !!},
            isNotificationSoundEnable: {!! json_encode(get_tenant_setting_from_db('whats-mark', 'enable_chat_notification_sound')) !!},
            open: false,
            sending: false,
            search: '',
            options: {!! json_encode($users) !!},
            selectedOptions: @json($selectedAgent ?? []),
            asignAgentView: '',
            isDeleteChatModal: false,
            isSupportAgentModal: false,
            isInitiateChatModal: false,
            isAdmin: {{ $user_is_admin ? 1 : 0 }},
            enableSupportAgent: {{ $enable_supportagent ? 1 : 0 }},
            conversationLimitReached: false,
            limitErrorMessage: '',

            // template variable
            editTemplateId: '',
            hInput: [],
            bInput: [],
            fileI: '',
            footerInputs: [],
            selectedOption: 0,
            previewUrl: '{{ !empty($filename) ? asset('storage/' . $filename) : '' }}',
            initiate_chat_loading: false,
            hasReachedBottom: false,
            _tempSelectedOptions: [],
            showSubmenu: false,
            search: '',
            getChatType:'',
            selectedTab: 'searching',
            reltypeFilter: '',
            agentsFilter: '',
            selectedGroup: '',
            selectedStatus: '',
            selectedSource: '',
            selectedReadStatus: '',
            noResultsMessage: '',
            rel_types: [
                { key: 'lead', value: 'Lead' },
                { key: 'customer', value: 'Customer' },
                { key: 'guest', value: 'Guest' },
            ],
            resetFilters() {

                // Reset all filter values
                this.selectedTab = 'searching';
                this.reltypeFilter = '';
                this.selectedSource = '';
                this.selectedStatus = '';
                this.selectedGroup = '';
                this.agentsFilter = '';
                this.selectedReadStatus = '';
                this.noResultsMessage = '';



                // Apply existing search if any
                if (this.searchText) {
                    this.searchChats();
                } else {
                    this.filterChats();
                }
                setTimeout(() => {
                    this.sortedChats = [...this.chats];
                }, 500);
                   // Reset to original chat list
            },
            handleAllFilters(e) {
                // Reset the sorted chats to trigger a fresh server-side filtered request
                this.sortedChats = [];
                this.hasReachedBottom = false;
                this.noResultsMessage = '';

                // Call server-side filtering
                this.getChats(0).then(chats => {
                    if (chats.length === 0) {
                        this.noResultsMessage = 'No chats found matching the selected filters.';
                    }
                }).catch(error => {
                    console.error('Error applying filters:', error);
                    this.noResultsMessage = 'Error loading filtered chats.';
                });
            },
            toggleOption(option) {
                if (this.selectedOptions.some(o => o.id === option.id)) {
                    this.selectedOptions = this.selectedOptions.filter(item => item.id !== option.id);
                } else {
                    this.selectedOptions.push(option);
                }

            },
            formatMessage(text) {

                text = this.highlightSearch(text);

                // Then replace newlines with <br> for display formatting
                return text.replace(/\n/g, '<br>');
            },
            heighlightMessage(text) {
                // First, highlight the search term if any
                return text = this.highlightSearch(text);
            },

            get filteredOptions() {
                return this.options.filter(option => {
                    const fullName = `${option.firstname} ${option.lastname}`.toLowerCase();
                    return fullName.includes(this.search.toLowerCase());
                });
            },
            openSupportAgentModal() {
                this._tempSelectedOptions = [...this.selectedOptions]; // Save current state
                this.isSupportAgentModal = true;
            },

            closeSupportAgentModal() {
                this.selectedOptions = [...this._tempSelectedOptions]; // Restore if not saved
                this.isSupportAgentModal = false;
                this.search = ''; // Clear search
                this.open = false; // Close dropdown
            },
            submitAgent(chatId) {
                const agentIds = this.selectedOptions.map(o => o.id); // Extract only IDs
                fetch(`/${this.subdomain}/assign-agent/${chatId}`, { // Only chatId in URL
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            agent_ids: agentIds // Send IDs in body instead of URL

                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.isSupportAgentModal = false;
                        showNotification(data.message, data.success ? 'success' : 'danger');
                        this.asignAgentView = data.agent_layout;
                        this._tempSelectedOptions = [...this.selectedOptions];
                    })
                    .catch(error => {
                        // Error handling for agent assignment
                    });
            },

            submitChat(chatId) {
                this.initiate_chat_loading = true;
                const formData = new FormData();
                // Append regular fields to FormData
                formData.append('chat_id', chatId);
                formData.append('template_id', this.editTemplateId);
                formData.append('header_inputs', JSON.stringify(this
                    .hInput))
                formData.append('body_inputs', JSON.stringify(this.bInput));
                formData.append('footer_inputs', JSON.stringify(this.footerInputs));
                formData.append('rel_type', this.relType);
                formData.append('buttons', JSON.stringify(this.buttons));

                // Append the actual file
                if (this.fileI) {
                    formData.append('file', this.fileI);
                }

                fetch(`/${this.subdomain}/initiate_chat/${chatId}`, {
                        method: "POST",
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.initiate_chat_loading = false;
                        this.isInitiateChatModal = false;
                        if (data.status) {
                            showNotification('Chat Initiated Successfully', 'success');
                            this.editTemplateId = '';
                            this.selectedOption = '';
                            this.campaignsSelected = false;
                            this.fileError = null;
                            this.campaignHeader = '';
                            this.campaignBody = '';
                            this.campaignFooter = '';
                            this.buttons = [];
                            this.inputType = 'text';
                            this.previewUrl = '';
                            this.previewFileName = '';
                            this.hInput = [];
                            this.bInput = [];
                            this.footerInputs = [];
                            this.headerInputErrors = [];
                            this.bodyInputErrors = [];
                            this.footerInputErrors = [];

                        } else {
                            let message = String(data.log_data.response_data).trim();
                            // Remove surrounding quotes if present
                            if (message.startsWith('"') && message.endsWith('"')) {
                                message = message.slice(1, -1).trim();
                            }
                            if (message) {
                                this.initiate_chat_loading = false;
                                showNotification(message, 'danger');
                                this.editTemplateId = '';
                                this.selectedOption = '';
                                this.previewUrl = '';
                                this.campaignsSelected = false;
                                this.fileError = null;
                                this.campaignHeader = '';
                                this.campaignBody = '';
                                this.campaignFooter = '';
                                this.buttons = [];
                                this.inputType = 'text';
                                this.previewFileName = '';
                                this.hInput = [];
                                this.bInput = [];
                                this.footerInputs = [];
                                this.headerInputErrors = [];
                                this.bodyInputErrors = [];
                                this.footerInputErrors = [];
                            }
                        }
                    })
                    .catch(error => {
                        // Error handling for chat submission
                    });
            },
            getUserInformation(type, type_id) {
                fetch(`/${this.subdomain}/user-information`, { // Only chatId in URL
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            type: type,
                            type_Id: type_id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            this.userInfo = data[0]; // Assign first user from the array
                        } else {
                            this.userInfo = null; // Reset if no data
                        }
                    })
                    .catch(error => {
                        // Error handling for user information fetch
                    });
            },

            handleModal() {
                this.isInitiateChatModal = true;

                this.$nextTick(() => {
                    const selectElement = document.getElementById('basic-select');

                    const fileInputs = [
                        document.getElementById('image_upload'),
                        document.getElementById('document_upload'),
                        document.getElementById('video_upload'),

                    ];

                    if (selectElement && selectElement.tomselect) {
                        // Reset the Tom Select instance to its default state (clear it)
                        selectElement.value = '';
                        selectElement.tomselect.setValue('');

                    } else {
                        // Fallback: Reset native select
                        if (this.$refs.campaignsChange) {
                            // Set the value to empty, which corresponds to the "nothing_selected" option
                            this.$refs.campaignsChange.value =
                                '';
                            this.$refs.campaignsChange.dispatchEvent(new Event(
                                'change')); // Trigger the change event
                        }
                    }

                    // Reset each file input if it exists
                    fileInputs.forEach(fileInput => {

                        if (fileInput) {
                            // Create a clone of the file input
                            const newFileInput = fileInput.cloneNode(true);
                            // Replace the old input with the clone (effectively resetting it)
                            if (fileInput.parentNode) {
                                fileInput.parentNode.replaceChild(newFileInput, fileInput);
                            }
                        }
                    });



                });

            },

            modalClose() {
                this.isInitiateChatModal = false;
                // Reset values after closing
                setTimeout(() => {
                    this.editTemplateId = '';
                    this.selectedOption = '';
                    this.campaignsSelected = false;
                    this.fileError = null;
                    this.campaignHeader = '';
                    this.campaignBody = '';
                    this.campaignFooter = '';
                    this.buttons = [];
                    this.inputType = 'text';
                    this.previewUrl = '';
                    this.previewFileName = '';
                    this.hInput = [];
                    this.bInput = [];
                    this.footerInputs = [];
                    this.headerInputErrors = [];
                    this.bodyInputErrors = [];
                    this.footerInputErrors = [];
                }, 500); // Wait for modal close animation
            },

            getAgentView(chatId) {

                fetch(`/${this.subdomain}/assign-agent-layout/${chatId}`, { // Only chatId in URL
                        method: "GET",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.asignAgentView = data.agent_layout;
                    })
                    .catch(error => {
                        // Error handling for agent view fetch
                    });
            },
            filteredCannedReplies() {
                return this.cannedReplies.filter(reply => {
                    return reply.added_from == this.currentUserId || reply.is_public;
                });
            },
            formatCannedReplies(text) {

                // Then replace newlines with <br> for display formatting
                return text.replace(/\n/g, '<br>');
            },
            uniqueWaNos() {
                return [...new Set(this.chats.map(chat => chat.wa_no))];
            },

            deleteMessage(messageId) {
                if (!this.selectedUser || !this.selectedUser.messages) return;
                this.selectedUser.messages = this.selectedUser.messages.filter(
                    message => message.id !== messageId
                );
                // Send a request to delete the message from the backend
                fetch(`/${this.subdomain}/remove-message/${messageId}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            // Error deleting message handled by notification
                        }
                        showNotification(data.message, data.success ? 'success' : 'danger');
                    })
                    .catch(error => {
                        // Error handling for message deletion
                    });

            },
            deleteChat(chatId) {
                if (!this.selectedUser) return;
                // Send a request to delete the message from the backend
                fetch(`/${this.subdomain}/remove-chat/${chatId}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the chat from the list
                            this.sortedChats = this.sortedChats.filter(chat => chat.id !== chatId);

                            // If the deleted chat was the selected one, reset selectedUser
                            if (this.selectedUser?.id === chatId) {
                                this.isDeleteChatModal = false;
                                this.selectedUser = null;
                                this.isShowUserChat = false;
                            }
                        }
                        showNotification(data.message, data.success ? 'success' : 'danger');
                    })
                    .catch(error => {
                        // Error handling for chat deletion
                    });
            },

            toggleMessageOptions(messageId) {
                this.activeMessageId = this.activeMessageId === messageId ? null : messageId;
            },
            sendAiRequest(menu, submenu) {
                this.loading = true;
                fetch(`/${this.subdomain}/ai-response`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            menu: menu,
                            submenu: submenu,
                            input_msg: this.textMessage
                        })
                    })
                    .then(response => response.json())
                    .then(data => {

                        this.loading = false;
                        this.openAiMenu = false;
                        this.showSubmenu = false;
                        this.search = " ";
                        if (data.success) {
                            this.textMessage = data.message;
                        } else {
                            showNotification(data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        // Error handling for AI request
                    });

            },
            checkOverdueAlert() {
                this.overdueAlert = false;
                this.remainingHours = 0;
                this.remainingMinutes = 0;

                let lastMsgTime = null;

                if (this.selectedUser && Array.isArray(this.selectedUser.messages)) {
                    const matchedMessages = this.selectedUser.messages.filter(
                        msg => msg.sender_id === this.selectedUser.receiver_id

                    );
                    // Sort by time_sent descending to get the latest
                    matchedMessages.sort((a, b) => new Date(b.time_sent) - new Date(a.time_sent));

                    lastMsgTime = matchedMessages.length > 0 ? matchedMessages[0].time_sent : null;
                }
                const timezone = '{{ get_tenant_setting_from_db('system', 'timezone') }}';

                if (lastMsgTime) {
                    const currentDate = new Date(new Date().toLocaleString("en-US", {
                        timeZone: timezone
                    }));
                    const messageDate = new Date(lastMsgTime);
                    const diffInMinutes = Math.floor((currentDate - messageDate) / (1000 * 60));
                    if (diffInMinutes >= 1440) {
                        this.overdueAlert = true;
                    } else {
                        this.remainingHours = Math.floor((1440 - diffInMinutes) / 60);
                        this.remainingMinutes = (1440 - diffInMinutes) % 60;
                    }
                }
            },

            // Filtering chats based on selected wa_no
            filterChats() {
                this.filteredChat = this.selectedWaNo === "*" ?
                    this.chats // Show all chats if "*" is selected
                    :
                    this.chats.filter(chat => chat.wa_no === this.selectedWaNo);

                // Ensure real-time UI update
                this.sortedChats = [...this.filteredChat];
            },

            searchChats() {
                if (this.searchText) {
                    const query = this.searchText.toLowerCase();
                    this.sortedChats = this.chats
                        .filter(chat =>
                            chat.name.toLowerCase().includes(query) ||
                            chat.last_message.toLowerCase().includes(query)
                        )
                        .sort((a, b) => new Date(b.time_sent) - new Date(a.time_sent)); // Keep sorting order
                } else {
                    this.sortedChats = [...this.chats].sort((a, b) => new Date(b.time_sent) - new Date(a
                        .time_sent)); // Reset if search is empty
                }
            },

            searchMessages() {
                if (this.searchMessagesText) {
                    const query = this.searchMessagesText.toLowerCase().trim();
                    const hasHtmlChars = /[<>]/.test(query);
                    const isHtmlTag = /^[a-z]+$/.test(query) && document.createElement(query).toString() !==
                        "[object HTMLUnknownElement]";

                    if (hasHtmlChars || isHtmlTag) {
                        this.searchError = "Searching for HTML tags is not allowed.";
                        this.selectedUser.messages.forEach(msg => msg.match = false);
                        this.matchedMessages = [];
                        this.updateSearchCounter(0, 0);
                        return;
                    } else {
                        this.searchError = "";
                    }

                    this.matchedMessages = [];

                    this.selectedUser.messages.forEach((msg, index) => {
                        const cleanMessage = sanitizeMessage(msg.message || '');
                        if (cleanMessage.toLowerCase().includes(query)) {
                            msg.match = true;
                            this.matchedMessages.push({
                                messageIndex: index,
                                position: cleanMessage.indexOf(query)
                            });
                        } else {
                            msg.match = false;
                        }
                    });

                    this.matchedMessages = [...new Set(this.matchedMessages)]; // Ensure unique matche
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const highlights = document.querySelectorAll('.highlight');
                            this.matchedMessages = Array.from(highlights);
                            this.matchIndex = 0;

                            if (this.matchedMessages.length > 0) {
                                this.scrollToMatch();
                            }

                            this.updateSearchCounter(
                                this.matchedMessages.length > 0 ? 1 : 0,
                                this.matchedMessages.length
                            );
                        }, 100);
                    });
                } else {
                    this.selectedUser.messages.forEach(msg => msg.match = false);
                    this.matchedMessages = [];
                    this.updateSearchCounter(0, 0);
                }
            },

            updateSearchCounter(current, total) {
                let counter = document.getElementById('search-counter');
                if (!counter) {
                    counter = document.createElement('span');
                    counter.id = 'search-counter';
                    counter.className = 'text-sm text-gray-600 dark:text-gray-400 ml-2';
                    const searchContainer = document.querySelector('.search-container');
                    if (searchContainer) {
                        searchContainer.appendChild(counter);
                    }
                }

                // Prevent unnecessary updates
                if (counter.textContent !== `${current} of ${total}`) {
                    counter.textContent = total > 0 ? `${current} of ${total}` : (this.searchMessagesText ?
                        'No matches' : '');
                }
            },
            scrollToMatch() {
                if (this.matchedMessages.length === 0) return;

                // Remove highlighting from all matches
                this.matchedMessages.forEach(el => {
                    el.classList.remove('active-highlight');
                });

                // Get the current highlight element
                const currentHighlight = this.matchedMessages[this.matchIndex];

                if (currentHighlight) {
                    // Add active class to current highlight
                    currentHighlight.classList.add('active-highlight');

                    // Scroll the highlight into view
                    currentHighlight.scrollIntoView({
                        behavior: "smooth",
                        block: "center"
                    });

                    // Update the counter
                    this.updateSearchCounter(this.matchIndex + 1, this.matchedMessages.length);
                }
            },

            nextMatch() {
                if (this.matchedMessages.length === 0) return;

                this.matchIndex = (this.matchIndex + 1) % this.matchedMessages.length;
                this.scrollToMatch();
            },

            prevMatch() {
                if (this.matchedMessages.length === 0) return;

                this.matchIndex = (this.matchIndex - 1 + this.matchedMessages.length) % this.matchedMessages
                    .length;
                this.scrollToMatch();
            },

            highlightSearch(text) {
                if (this.searchError !== '' || !this.searchMessagesText || !text) return text;

                const sanitizedText = sanitizeMessage(text);
                const query = this.searchMessagesText.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');

                // Replace with highlight spans
                return sanitizedText.replace(
                    new RegExp(`(${query})`, "gi"),
                    `<span class="bg-warning-300 dark:bg-warning-600 px-1 py-0.5 rounded highlight">$1</span>`
                );
            },
            resetSearchState() {
                this.searchMessagesText = '';
                this.searchError = '';
                this.matchedMessages = [];
                this.matchIndex = 0;

                // Clear highlight matches
                if (this.selectedUser?.messages?.length) {
                    this.selectedUser.messages.forEach(msg => msg.match = false);
                }

                // Remove counter if it exists
                const counter = document.getElementById('search-counter');
                if (counter) {
                    counter.remove();
                }

                // Hide search input UI
                this.messagesSearch = false;
            },

            openDropdown(event) {
                this.showReactionList = true;

            },
            toggleRecording() {
                if (!this.isRecording) {
                    this.startRecording();
                } else {
                    this.stopRecording();
                }
            },

            startRecording() {
                if (!this.recorder) {
                    this.recorder = new Recorder({
                        type: "mp3",
                        sampleRate: 16000,
                        bitRate: 16,
                        onProcess: (buffers, powerLevel, bufferDuration, bufferSampleRate) => {
                            // Optional real-time updates
                        }
                    });
                }
                this.recorder.open(() => {
                    this.isRecording = true;
                    this.recorder.start();
                }, (err) => {
                    console.error("Failed to start recording:", err);
                });
            },

            stopRecording() {
                if (this.recorder && this.isRecording) {
                    this.recorder.stop((blob) => {
                        this.recorder.close();
                        this.isRecording = false;
                        this.audioBlob = blob;
                        this.recordedAudio = URL.createObjectURL(blob);
                        this.sendMessage();
                    }, (err) => {
                        console.error("Failed to stop recording:", err);
                    });
                }
            },

            sendMessage() {
                if (this.sending) return;
                if (!this.textMessage.trim() && !this.attachment && !this.audioBlob) return;
                this.sending = true; // Disable button
                let formData = new FormData();
                formData.append('id', this.selectedUser.id);
                formData.append('type', this.selectedUser.type);
                formData.append('type_id', this.selectedUser.type_id);
                formData.append('message', this.textMessage.trim() || '');
                formData.append('ref_message_id', this.replyTo ? this.replyTo.messasgeID : '');

                if (this.attachment) {
                    const keyName = this.attachmentType; // image, video, or document
                    formData.append(keyName, this.attachment, this.fileName);
                }

                if (this.audioBlob) {
                    formData.append('audio', this.audioBlob, 'audio.mp3');
                }
                this.sendFormData(formData);
            },

            sendFormData(formData) {
                this.sendingErrorMessage = '';
                this.limitErrorMessage = '';
                this.conversationLimitReached = false;

                fetch(`/${this.subdomain}/send-message`, {
                        method: 'POST',
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    })
                    .then(response => {
                        // Check if it's a conversation limit error (429 status)
                        if (response.status === 429) {
                            return response.json().then(data => {
                                throw new Error(JSON.stringify({
                                    isLimitError: true,
                                    message: data.error ||
                                        '{{ t('conversation_limit_upgrade_message') }}'
                                }));
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success === false) {
                            if (data.limit_reached || data.error.includes('limit')) {
                                this.conversationLimitReached = true;
                                this.limitErrorMessage = data.error ||
                                    'Conversation limit reached. Please upgrade your plan to continue messaging.';
                                showNotification(this.limitErrorMessage, 'warning', 7000);
                            } else {
                                this.sendingErrorMessage = data.message || 'Failed to send message';
                                setTimeout(() => {
                                    this.sendingErrorMessage = '';
                                }, 5000);
                            }
                            return;
                        }
                        this.textMessage = '';
                        this.sendingErrorMessage = '';
                        this.limitErrorMessage = '';
                        this.conversationLimitReached = false;
                        this.attachment = null;
                        this.audioBlob = null;
                        this.removePreview();
                        this.cancelReply();
                        this.scrollToBottom();
                    })
                    .catch(error => {
                        console.error('Error sending message:', error);

                        try {
                            const errorData = JSON.parse(error.message);
                            if (errorData.isLimitError) {
                                this.conversationLimitReached = true;
                                this.limitErrorMessage = errorData.message;
                                showNotification(this.limitErrorMessage, 'warning', 7000);
                                return;
                            }
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                        }

                        this.sendingErrorMessage = 'Error sending message:',JSON.parse(error.message);
                      
                        setTimeout(() => {
                            this.sendingErrorMessage = '';
                        }, 5000);
                    })
                    .finally(() => {
                        this.sending = false; // Re-enable button
                    });
            },

            sanitizeLastMessage(content) {
                return sanitizeMessage(content).replace(/<\/?[^>]+(>|$)/g, ""); // Sanitize & strip HTML
            },
           
            getOriginalMessage(refMessageId) {
                if (typeof(this.selectedUser.messages) === "object") {
                    const message = this.selectedUser.messages.find(msg => msg.message_id === refMessageId) || {};
                    return {
                        ...message,
                        message: message.message,
                        assets_url: message.url || ''
                    };
                }
            },
            getMergeFields(chatType) {
            this.getChatType = chatType;
                fetch(`/${this.subdomain}/load-mergefields/${chatType}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            type: chatType,
                        })
                    })
                    .then(response => response.json())
                    .then(data => {

                        this.mergeFields = data;
                    })
                    .catch(error => console.error("Fetch error:", error));
            },
            handleTributeEvent() {
                setTimeout(() => {
                    if (typeof window.Tribute === 'undefined') {
                        return;
                    }
                    let mentionableEl = document.querySelector('.mentionable');

                    if (!mentionableEl) {
                        return; // Exit if element doesn't exist
                    }
                    // Initialize Tribute with updated mergeFields
                    let tribute = new window.Tribute({
                        trigger: '@',
                        values: this.mergeFields,
                    });
                    tribute.attach(mentionableEl);
                    mentionableEl.setAttribute('data-tribute', 'true'); // Mark as initialized

                    document.querySelectorAll('.tribute-container').forEach((el) => el.remove());
                }, 1000);
            },
            handleEnterKey(event) {
                if (this.sending) return;
                // Check if Tribute dropdown is active
                let tributeDropdown = document.querySelector('.tribute-container');

                if (tributeDropdown && tributeDropdown.style.display === 'block') {
                    event.preventDefault(); // Prevent sending the message when Tribute is open
                    return;
                }

                if (event.keyCode === 13) {
                    if (event.shiftKey) {
                        // Shift+Enter adds new line
                        event.preventDefault();
                        this.textMessage += '\n';
                    } else {
                        // Enter sends message
                        event.preventDefault();
                        if (this.textMessage.trim() !== '') {
                            this.sendMessage();
                        }
                    }
                }
            },

            canSendMessage() {
                return !this.conversationLimitReached && this.selectedUser && !this.sending;
            },

            // Updated getChatMessages method with async/await
            async getChatMessages(chatId, lastMessageId = 0) {

                try {
                    let url = `/${this.subdomain}/chat_messages/${chatId}`;
                    if (lastMessageId > 0) {
                        url += `/${lastMessageId}`;
                    }
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json'

                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Error fetching messages:', error);
                    return []; // Return empty array on error
                }
            },
            // Updated getChats method with async/await
            async getChats(lastChatId = 0) {
                try {
                    // Build filter params object
                    const filterParams = {
                        lastChatId: lastChatId,
                        relationType: this.reltypeFilter || '',
                        sourceId: this.selectedSource || '',
                        statusId: this.selectedStatus || '',
                        groupId: this.selectedGroup || '',
                        agentId: this.agentsFilter || '',
                        readStatus: this.selectedReadStatus || '',
                    };

                    let url = `/${this.subdomain}/chat_data`;


                    // Initialize sortedChats as empty array if undefined
                    if (!Array.isArray(this.sortedChats)) {
                        this.sortedChats = [];
                    }

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(filterParams)
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const data = await response.json();
                    console.log('Received response:', data);

                    // Handle new response format with metadata
                    let chats = [];
                    if (data.chats && Array.isArray(data.chats)) {
                        chats = data.chats;

                        // Update metadata if provided (for initial load)
                        if (data.metadata && lastChatId === 0) {
                            if (data.metadata.sources) {
                                this.sources = data.metadata.sources;
                            }
                            if (data.metadata.statuses) {
                                this.statuses = data.metadata.statuses;
                            }
                            if (data.metadata.groups) {
                                this.groups = data.metadata.groups;
                            }
                            if (data.metadata.users) {
                                this.users = data.metadata.users;
                                this.options = data.metadata.users;
                            }
                        }
                    } else if (Array.isArray(data)) {
                        // Backward compatibility for old format
                        chats = data;
                    }

                    console.log('Processed chats:', chats.length, 'items');

                    // Ensure chats is an array and has proper structure
                    if (!Array.isArray(chats)) {
                        console.error('Expected array but received:', typeof chats);
                        return [];
                    }

                    // Add unique identifiers and ensure no duplicates
                    const newChats = chats.filter(newChat =>
                        !this.sortedChats.some(existingChat => existingChat.id === newChat.id)
                    );

                    // Only append new chats to avoid duplicates
                    if (lastChatId === 0) {
                        // First load - replace all chats
                        this.sortedChats = [...chats];
                    } else {
                        // Pagination - append only new chats
                        this.sortedChats = [...this.sortedChats, ...newChats];
                    }
                    return chats;
                } catch (error) {
                    console.error('Error fetching messages:', error);
                    return []; // Return empty array on error
                }
            },

            async onSidebarScroll(event) {
                const chatSidebarBox = this.$refs.chatSidebar;
                const isAtBottom = chatSidebarBox.scrollHeight - chatSidebarBox.scrollTop <= chatSidebarBox
                    .clientHeight + 1;

                if (isAtBottom && !this.hasReachedBottom && !this.loadingChats) {
                    this.hasReachedBottom = true;
                    this.loadingChats = true;

                    // Show loader
                    const loaderWrapper = document.createElement('div');
                    loaderWrapper.className = 'flex justify-center w-full my-2';
                    loaderWrapper.id = 'loader-wrapper';

                    const loader = document.createElement('div');
                    loader.className =
                        'text-center py-2 px-4 rounded-full bg-white dark:bg-gray-700 shadow-sm inline-flex items-center transition-all duration-300';
                    loader.innerHTML =
                        '<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-solid border-current border-r-transparent mr-2"></span><span class="text-gray-700 dark:text-gray-200">Loading chats...</span>';

                    loaderWrapper.appendChild(loader);
                    chatSidebarBox.appendChild(loaderWrapper);
                    await this.$nextTick();
                    chatSidebarBox.scrollTop = chatSidebarBox.scrollHeight;

                    try {
                     // Get the last chat ID more safely
            const lastChatId = this.sortedChats && this.sortedChats.length > 0
                ? (this.sortedChats[this.sortedChats.length - 1]?.interaction_id ?? this.sortedChats[this.sortedChats.length - 1]?.id ?? 0)
                : 0;

                        if (!lastChatId) {
                            document.getElementById('loader-wrapper')?.remove();
                            this.loadingChats = false;
                            return;
                        }

                        await new Promise(resolve => setTimeout(resolve, 800)); // for smoother UX
                        const newerChats = await this.getChats(lastChatId);
                        document.getElementById('loader-wrapper')?.remove();

                        if (newerChats.length === 0) {
                            const noMore = document.createElement('div');
                            noMore.className = 'w-full flex justify-center my-3 transition-opacity duration-300';
                            noMore.innerHTML = `
                                <div class="text-center py-1.5 px-4 rounded-full bg-white dark:bg-gray-800 text-sm text-gray-500 dark:text-gray-400 shadow-sm">
                                    No more messages
                                </div>`;
                            chatSidebarBox.appendChild(noMore);
                            await this.$nextTick();
                            chatSidebarBox.scrollTop = chatSidebarBox.scrollHeight;
                            setTimeout(() => {
                                noMore.classList.add('opacity-0');
                                setTimeout(() => noMore.remove(), 300);
                            }, 2500);
                        }

                    } catch (error) {
                        console.error('Error loading messages:', error);
                        document.getElementById('loader-wrapper')?.remove();
                    } finally {
                        this.loadingChats = false; // ✅ allow future fetch
                    }
                }

                if (!isAtBottom && this.hasReachedBottom) {
                    this.hasReachedBottom = false;
                }
            },

            refreshConversationLimit() {
                this.conversationLimitReached = false;
                this.limitErrorMessage = '';
                showNotification('Conversation limit refreshed. You can try sending messages again.', 'success');
            },

            // Updated checkScrollTop method with async/await
            async checkScrollTop(chatId) {
                let chatBox = this.$refs.chatContainer;
                if (chatBox.scrollTop === 0) {
                    try {
                        const oldScrollHeight = chatBox.scrollHeight;

                        // Loader UI
                        const wrapperDiv = document.createElement('div');
                        wrapperDiv.className = 'flex justify-center w-full my-2';
                        wrapperDiv.id = 'loader-wrapper';

                        const loader = document.createElement('div');
                        loader.className =
                            'text-center py-2 px-4 rounded-full bg-white dark:bg-gray-700 shadow-sm inline-flex items-center transition-all duration-300';
                        loader.id = 'scroll-loader';
                        loader.innerHTML =
                            '<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-solid border-current border-r-transparent align-[-0.125em] motion-reduce:animate-[spin_1.5s_linear_infinite] mr-2"></span><span class="text-gray-700 dark:text-gray-200">Loading messages...</span>';

                        wrapperDiv.appendChild(loader);
                        chatBox.prepend(wrapperDiv);

                        // **Fix: Ensure selectedUser.messages exists before accessing ID**
                        const firstMessageId = this.selectedUser?.messages?.length ? this.selectedUser
                            .messages[0]
                            .id : null;

                        if (!firstMessageId) {
                            console.warn('No messages available to load older ones.');
                            document.getElementById('loader-wrapper')?.remove();
                            return;
                        }

                        // Fetch older messages
                        const olderMessages = await this.getChatMessages(chatId, firstMessageId);

                        document.getElementById('loader-wrapper')?.remove();

                        if (olderMessages.length > 0) {
                            const transitionContainer = document.createElement('div');
                            transitionContainer.className = 'opacity-0 transition-opacity duration-500';
                            transitionContainer.id = 'new-messages-container';
                            chatBox.prepend(transitionContainer);

                            this.selectedUser.messages = [...olderMessages, ...this.selectedUser.messages];

                            await this.$nextTick();
                            chatBox.scrollTop = chatBox.scrollHeight - oldScrollHeight;

                            setTimeout(() => {
                                const container = document.getElementById('new-messages-container');
                                if (container) {
                                    container.classList.remove('opacity-0');
                                    container.classList.add('opacity-100');

                                    setTimeout(() => {
                                        if (container && container.parentNode) {
                                            container.replaceWith(...container.childNodes);
                                        }
                                    }, 500);
                                }
                            }, 10);
                        } else {
                            const noMoreWrapper = document.createElement('div');
                            noMoreWrapper.className = 'flex justify-center w-full my-2';
                            noMoreWrapper.id = 'no-more-wrapper';

                            const noMoreElement = document.createElement('div');
                            noMoreElement.className =
                                'text-center py-2 px-4 rounded-full bg-gray-100 dark:bg-gray-800 text-sm text-gray-500 dark:text-gray-400 shadow-sm inline-block transition-all duration-300';
                            noMoreElement.id = 'no-more-messages';
                            noMoreElement.innerText = 'No more messages';

                            noMoreWrapper.appendChild(noMoreElement);
                            chatBox.prepend(noMoreWrapper);

                            setTimeout(() => {
                                const element = document.getElementById('no-more-messages');
                                if (element) {
                                    element.classList.add('opacity-0');
                                    setTimeout(() => {
                                        document.getElementById('no-more-wrapper')?.remove();
                                    }, 300);
                                }
                            }, 2000);
                        }
                    } catch (error) {
                        console.error('Error loading older messages:', error);
                        document.getElementById('loader-wrapper')?.remove();

                        const errorWrapper = document.createElement('div');
                        errorWrapper.className = 'flex justify-center w-full my-2';
                        errorWrapper.id = 'error-wrapper';

                        const errorElement = document.createElement('div');
                        errorElement.className =
                            'text-center py-2 px-4 rounded-full bg-danger-50 dark:bg-danger-900/30 text-sm text-danger-600 dark:text-danger-400 shadow-sm inline-block transition-all duration-300';
                        errorElement.id = 'load-error';
                        errorElement.innerText = 'Failed to load messages';

                        errorWrapper.appendChild(errorElement);
                        chatBox.prepend(errorWrapper);

                        setTimeout(() => {
                            const element = document.getElementById('load-error');
                            if (element) {
                                element.classList.add('opacity-0');
                                setTimeout(() => {
                                    document.getElementById('error-wrapper')?.remove();
                                }, 300);
                            }
                        }, 3000);
                    }
                }
            },
            selectChat(chat) {
                this.selectedUser = chat;
                this.isShowUserChat = true;
                this.isShowChatMenu = false;
                this.overdueAlert = false;
                this.loading = true; // Start loading indicator
                this.getAgentView(chat.id);
                this.chatId = this.selectedUser.id;
                this.getUserInformation(chat.type, chat.type_id);
                //this.initiateChat(chat.type, chat.type_id);
                this.getMergeFields(chat.type);

                // Clear messages immediately to prevent showing the old chat
                this.messages = [];
                this.getChatMessages(chat.id).then((data) => {
                    this.messages = data; // Update messages after fetching
                    this.selectedUser.messages = this.messages; // Ensure UI updates
                    this.handleTributeEvent();
                    // Hide unread count for this specific chat
                    this.$nextTick(() => {
                        chat.hideUnreadCount = true;
                    });
                    this.loading = false; // Hide loader only after everything is done
                    this.checkOverdueAlert();
                    this.scrollToBottom();
                });

                // Ensure agent data exists before parsing
                if (this.selectedUser.agent && this.selectedUser.agent !== "null") {
                    let agentData = JSON.parse(this.selectedUser.agent); // Parse JSON string safely
                    let agentIds = agentData.agents_id ? agentData.agents_id.split(',').map(id => id.trim()) : [];

                    // Find matching options
                    this.selectedOptions = this.options.filter(option => agentIds.includes(option.id
                        .toString()));
                } else {
                    this.selectedOptions = []; // Reset if agent data is missing
                }


                this.textMessage = '';
                this.attachment = null;
                this.audioBlob = null;
                this.removePreview();
                this.cancelReply();
                this.scrollToBottom();

            },

            countUnreadMessages(chatId) {
                const interaction = this.sortedChats ? this.sortedChats.find(inter => inter.id === chatId) :
                    undefined;

                if (interaction) {
                    interaction.messages = this.messages; // Ensure this only runs if interaction exists
                    return interaction.unreadmessagecount || 0;
                }

                return 0;
            },


            initialize() {
                this.$watch('showSubmenu', (value) => {
                    if (!value) {
                        this.search = '';
                    }
                });
                this.sortedChats = [...this.chats].sort((a, b) => {
                    // Ensure messages array exists, else default to an empty array
                    let latestTimeA = new Date(a.time_sent || 0);
                    let latestTimeB = new Date(b.time_sent || 0);

                    return latestTimeB - latestTimeA; // Sorting in descending order
                });

                window.addEventListener('updateTextMessage', (event) => {
                    this.textMessage = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                    this.loading = false;
                });

                this.initializePusher();
            },


            replyToMessage(message) {
                if (!message) return;

                let textContent = "";
                let urlContent = "";
                let messageType = "";

                if (typeof message === "string") {
                    textContent = message;
                } else {
                    // Check if the message contains text or a URL
                    textContent = message.message || "";
                    urlContent = message.url || "";
                    messageType = message.type || "";
                }

                // Strip HTML tags if it's a text message
                if (textContent) {
                    textContent = textContent.replace(/<[^>]*>?/gm, '');
                    let maxLength = 100;
                    if (textContent.length > maxLength) {
                        textContent = textContent.substring(0, maxLength) + "...";
                    }
                }

                // Store data properly
                this.replyTo = {
                    text: textContent,
                    url: urlContent,
                    type: messageType,
                    messasgeID: message.message_id
                };
                this.activeMessageId = null;
                this.scrollToBottom();
            },


            cancelReply() {
                this.replyTo = null; // Clear reply message
            },

            scrollToBottom() {
                if (this.isShowUserChat) {
                    setTimeout(() => {
                        const element = document.querySelector('.chat-conversation-box');
                        if (element) {
                            // Scroll smoothly to the bottom
                            element.scrollTo({
                                top: element.scrollHeight,
                                behavior: 'smooth'
                            });
                        }
                    }, 0);
                }
            },
            scrollToMessage(ref_message_id) {
                if (!ref_message_id) {
                    console.error("Error: ref_message_id is null or undefined.");
                    return;
                }
                // Find the message element dynamically
                const targetMessage = document.querySelector(`[data-message-id="${ref_message_id}"]`);

                if (!targetMessage) {
                    console.error(`Error: No element found for message ID '${ref_message_id}'`);
                    return;
                }

                // Get the parent wrapper (entire message container)
                const messageWrapper = targetMessage.closest('.message-item');

                if (!messageWrapper) {
                    console.error("Error: Message wrapper not found.");
                    return;
                }

                // Smooth scroll
                messageWrapper.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                // Add grayscale effect
                messageWrapper.classList.add('contrast-50', 'transition-all', 'duration-500');

                // Remove the effect after 1 second
                setTimeout(() => {
                    messageWrapper.classList.remove('contrast-50');
                }, 1000);
            },

            selectFileType(type) {
                this.showAttach = false;
                // Trigger corresponding file input
                if (type === 'image') {
                    document.getElementById('image_upload').click();
                } else if (type === 'document') {
                    document.getElementById('document_upload').click();
                } else if (type === 'video') {
                    document.getElementById('video_upload').click();
                }
            },

            handleFilePreview(event, type) {
                const file = event.target.files[0];
                if (!file) return;

                // Get allowed extensions and max size
                const allowedExtensions = this.metaExtensions[type].extension.replace(/\s/g, '').split(',');
                const maxSize = this.metaExtensions[type].size * 1024 * 1024; // Convert MB to bytes

                // Get file extension
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

                // Validate file extension
                if (!allowedExtensions.includes(fileExtension)) {
                    showNotification(`Invalid file type`, 'danger');

                    return;
                }

                // Validate file size
                if (file.size > maxSize) {
                    showNotification(
                        `File size exceeds the limit! Max size: ${this.metaExtensions[type].size}MB`,
                        'danger');

                    return;
                }

                // If valid, proceed
                this.previewType = type;
                this.previewUrl = URL.createObjectURL(file);
                this.fileName = file.name;
                this.attachment = file;
                this.attachmentType = type;
            },

            removePreview() {
                this.previewUrl = '';
                this.previewType = '';
                this.fileName = '';
                this.attachment = null;
                this.attachmentType = '';
            },
            shouldShowDate(currentMessage, previousMessage) {
                if (!previousMessage || !currentMessage) return true;
                return this.formatDate(currentMessage.time_sent) !== this.formatDate(previousMessage
                    .time_sent);
            },
            formatDate(dateString) {
                const wb_date = new Date(dateString);
                const wb_options = {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                };
                return wb_date.toLocaleDateString('en-GB', wb_options).replace(' ', '-').replace(' ', '-');
            },
            formatTime(time) {

                if (!time) {
                    return "--"; // Placeholder for missing time
                }
                const messageDate = new Date(time);

                if (isNaN(messageDate.getTime())) {

                    return "Invalid time";
                }
                // Return only the time in HH:MM AM/PM format
                return messageDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true,
                    timeZone: "{{ get_tenant_setting_from_db('system', 'timezone') }}"
                });
            },
            formatLastMessageTime(timestamp) {
                // If no timestamp is provided, return empty string
                if (!timestamp) return '';

                // Parse the timestamp (assuming format: YYYY-MM-DD HH:MM:SS)
                const messageDate = new Date(timestamp);

                // Get current date for comparison
                const now = new Date();

                // Check if the date is valid
                if (isNaN(messageDate.getTime())) {
                    return timestamp; // Return original if parsing failed
                }

                // Format time to 12-hour format with AM/PM
                const formatTimeOnly = (date) => {
                    return date.toLocaleString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true,
                        timeZone: "{{ get_tenant_setting_from_db('system', 'timezone') }}"
                    });
                };

                // Check if it's today
                if (
                    messageDate.getDate() === now.getDate() &&
                    messageDate.getMonth() === now.getMonth() &&
                    messageDate.getFullYear() === now.getFullYear()
                ) {
                    // Today - show only time (e.g., "3:39 PM")
                    return formatTimeOnly(messageDate);
                }

                // Check if it's yesterday
                const yesterday = new Date(now);
                yesterday.setDate(now.getDate() - 1);

                if (
                    messageDate.getDate() === yesterday.getDate() &&
                    messageDate.getMonth() === yesterday.getMonth() &&
                    messageDate.getFullYear() === yesterday.getFullYear()
                ) {
                    // Yesterday - show "Yesterday" and time
                    return `Yesterday`;
                }

                // It's older than yesterday - show date with year (e.g., "Mar 15, 2024")
                // Check if it's the current year
                if (messageDate.getFullYear() === now.getFullYear()) {
                    // Same year - show date without year (e.g., "Mar 15")
                    return messageDate.toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric'
                    });
                } else {
                    // Different year - show date with year (e.g., "Mar 15, 2024")
                    return messageDate.toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });
                }
            },
            initializeUserInteractionTracking() {
                // Add event listeners for user interaction
                const markInteracted = () => {
                    this.hasUserInteracted = true;
                    // Remove the listeners once we've detected interaction
                    document.removeEventListener('click', markInteracted);
                    document.removeEventListener('keydown', markInteracted);
                    document.removeEventListener('touchstart', markInteracted);
                };

                document.addEventListener('click', markInteracted);
                document.addEventListener('keydown', markInteracted);
                document.addEventListener('touchstart', markInteracted);
            },
            playNotificationSound() {
                // Only play if notifications are enabled
                if (!this.isNotificationSoundEnable) return;
                // User has interacted, play sound immediately
                const audio = new Audio("{{ asset('audio/whatsapp_notification.mp3') }}");
                audio.play().catch(error => console.error("Audio play failed:", error));
            },

            initializePusher() {
                // Initialize Pusher with your app key and cluster
                const pusher = new Pusher(window.pusherConfig.key, {
                    cluster: window.pusherConfig.cluster,
                    encrypted: true,
                });
                // Subscribe to the 'interactions-channel'
                const channel = pusher.subscribe('whatsmark-saas-chat-channel');

                // Listen for the 'interaction-update' event
                channel.bind('whatsmark-saas-chat-event', (data) => {
                    // Update interactions based on real-time data from Pusher
                    this.appendNewChats(data.chat);

                    // Trigger desktop notification for new chat messages
                    this.triggerChatDesktopNotification(data.chat);
                });
            },
            appendNewChats(newChats) {
                const existingInteractions = [...this.sortedChats]; // Existing interactions array

                const index = existingInteractions.findIndex(chat => chat.id === newChats
                    .id); //matching interaction id to newChats id
                let isNewMessage = false;
                if (index !== -1) { //interaction IDs match, replace the whole existing message with the new message
                    const existingInteraction = existingInteractions[index];

                    // Create a new object that contains all properties from newChats except messages
                    const updatedInteraction = {
                        ...existingInteraction, // Existing properties
                        ...newChats, // Spread newChats properties
                        messages: existingInteraction.messages // Keep the original messages for now
                    };
                    // Find index of matching message_id
                    const find_msg_index = Array.isArray(existingInteraction.messages) ?
                        existingInteraction.messages.findIndex(interaction =>
                            Array.isArray(newChats.messages) &&
                            newChats.messages.some(newMsg => interaction.message_id === newMsg.message_id)
                        ) : -1;
                    //matching interaction messages id to newChats messages id
                    if (find_msg_index !== -1) {
                        // If IDs match, replace the whole existing message with the new message
                        existingInteraction.messages[find_msg_index] = {
                            ...newChats.messages[0]
                        };
                    } else if (this.selectedUser.id == existingInteraction.id) {
                        existingInteraction.messages.push(...newChats.messages);
                    }
                    isNewMessage = true;
                    existingInteractions[index] = updatedInteraction;
                    this.countUnreadMessages(existingInteractions[index].id);
                    this.initializeUserInteractionTracking();
                } else {
                    // Ensure newChats.messages is an array or initialize it as an empty array
                    if (!Array.isArray(newChats.messages)) {
                        newChats.messages = [newChats.messages];
                    }
                    // If the interaction id does not exist, push newChats directly
                    existingInteractions.push({
                        ...newChats,
                        messages: [...newChats.messages] // Ensure messages is properly handled
                    });
                    isNewMessage = true;
                    if (existingInteractions[index]) {
                        this.countUnreadMessages(existingInteractions[index].id);
                    }

                    this.initializeUserInteractionTracking();
                }

                if (isNewMessage && this.isNotificationSoundEnable) {
                    this.playNotificationSound();
                }

                // Now sort the `existingInteractions` array by `time_sent`
                existingInteractions.sort((a, b) => {
                    // Find the latest message by comparing all time_sent values
                    let latestTimeA = new Date(a.time_sent || 0);
                    let latestTimeB = new Date(b.time_sent || 0);
                    return latestTimeB - latestTimeA; // Sorting in descending order
                });
                this.sortedChats = existingInteractions;

                if (!this.isAdmin && this.enableSupportAgent == 1) {
                    const staff_id = @json($login_user);
                    const filteredNewInteractions = existingInteractions.filter(interaction => {
                        const chatagent = interaction.agent;
                        if (!chatagent) return false;
                        if (chatagent) {
                            const preResponse = JSON.parse(chatagent);
                            const temAgentId = preResponse.agents_id;
                            const agentIds = temAgentId ? temAgentId.split(",").map(Number) : []
                            const assignIds = preResponse.assign_id ? preResponse.assign_id : ''
                            // Check if `staff_id` is included in either `agentIds` or `assignIds`
                            return agentIds.includes(staff_id) || assignIds == staff_id;
                        }
                        return [];
                    });
                    this.sortedChats = this.sortedChats.filter(
                        existing => filteredNewInteractions.some(newInteraction => newInteraction.id ===
                            existing
                            .id)
                    );

                } else {
                    // Append new interactions for admins
                    this.sortedChats = existingInteractions;
                }
            },

            // Update contact status
            updateContactStatus(statusId, statusName, statusColor) {
                if (!this.userInfo?.id) {
                    console.error('No contact selected');
                    return;
                }

                const data = {
                    contact_id: this.userInfo.id,
                    status_id: statusId,
                    _token: '{{ csrf_token() }}'
                };

                fetch('{{ tenant_route("tenant.update_contact_status") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        // Update the userInfo status
                        if (this.userInfo) {
                            this.userInfo.status = {
                                id: statusId,
                                name: statusName,
                                color: statusColor
                            };
                        }

                        // Show success notification
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                message: `Contact status updated to "${statusName}"`,
                                type: 'success'
                            }
                        }));

                    } else {
                        console.error('Failed to update status:', result.message);

                        // Show error notification
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                message: result.message || 'Failed to update contact status',
                                type: 'danger'
                            }
                        }));
                    }
                })
                .catch(error => {
                    console.error('Error updating status:', error);

                    // Show error notification
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            message: 'An error occurred while updating contact status',
                            type: 'danger'
                        }
                    }));
                });
            },

            // Update contact source
            updateContactSource(sourceId, sourceName) {
                if (!this.userInfo?.id) {
                    console.error('No contact selected');
                    return;
                }

                const data = {
                    contact_id: this.userInfo.id,
                    source_id: sourceId,
                    _token: '{{ csrf_token() }}'
                };

                fetch(`/${this.subdomain}/update-contact-source`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        // Update the userInfo source
                        if (this.userInfo) {
                            this.userInfo.source = {
                                id: sourceId,
                                name: sourceName
                            };
                        }

                        // Show success notification
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                message: `Contact source updated to "${sourceName}"`,
                                type: 'success'
                            }
                        }));

                    } else {
                        console.error('Failed to update source:', result.message);

                        // Show error notification
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                message: result.message || 'Failed to update contact source',
                                type: 'danger'
                            }
                        }));
                    }
                })
                .catch(error => {
                    console.error('Error updating source:', error);

                    // Show error notification
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            message: 'An error occurred while updating contact source',
                            type: 'danger'
                        }
                    }));
                });
            },

            // Trigger desktop notification for new chat messages
            triggerChatDesktopNotification(chatData) {
                // Check if desktop notifications are enabled in tenant settings
                const desktopNotifyEnabled = window.pusherConfig?.desktop_notification;
                if (!desktopNotifyEnabled) {
                    return; // Desktop notifications are disabled
                }

                // Check if this is a new incoming message (not sent by staff)
                if (!chatData.messages || !Array.isArray(chatData.messages)) {
                    return;
                }

                const latestMessage = chatData.messages[chatData.messages.length - 1];

                // Only show notifications for messages not sent by staff (incoming messages from customers)
                // Use the enhanced notification metadata if available
                const isIncoming = chatData.notification?.is_incoming ?? (latestMessage && latestMessage.staff_id === 0);

                if (!latestMessage || !isIncoming) {
                    return; // This is a staff message or not incoming
                }

                // Check if the chat window is not currently focused to avoid spam
                if (document.hasFocus() && this.selectedChatId == chatData.id) {
                    return; // User is already viewing this chat
                }

                // Extract contact name and message preview
                const contactName = chatData.name || 'WhatsApp Contact';
                const messageText = this.getMessagePreview(latestMessage);
                const phoneNumber = chatData.wa_no || 'Unknown';

                // Use pusherManager to show desktop notification
                if (window.Alpine && Alpine.store('pusherManager')) {
                    Alpine.store('pusherManager').showDesktopNotification(
                        `💬 New message from ${contactName}`,
                        {
                            message: messageText,
                            body: `${phoneNumber}: ${messageText}`,
                            icon: window.pusherConfig?.notification_icon || '/img/wm-notification.png',
                            tag: `chat-${chatData.id}`, // Prevent duplicate notifications
                            requireInteraction: false,
                            autoDismiss: window.pusherConfig?.auto_dismiss_notification || 8000,
                            onclick: () => {
                                // Focus window and select the chat when notification is clicked
                                window.focus();
                                this.loadUserChat(chatData.id, contactName, phoneNumber, chatData);
                                this.selectedChatId = chatData.id;
                            }
                        }
                    );
                } else {
                    // Fallback to basic notification if pusherManager is not available
                    if ('Notification' in window && Notification.permission === 'granted') {
                        const notification = new Notification(`💬 New message from ${contactName}`, {
                            body: `${phoneNumber}: ${messageText}`,
                            icon: window.pusherConfig?.notification_icon || '/img/wm-notification.png',
                            tag: `chat-${chatData.id}`,
                            requireInteraction: false
                        });

                        notification.onclick = () => {
                            window.focus();
                            this.loadUserChat(chatData.id, contactName, phoneNumber, chatData);
                            this.selectedChatId = chatData.id;
                            notification.close();
                        };

                        // Auto-close after specified time
                        setTimeout(() => {
                            notification.close();
                        }, window.pusherConfig?.auto_dismiss_notification || 8000);
                    } else {
                        // Try to request permission if not granted
                        if ('Notification' in window && Notification.permission !== 'granted' && Notification.permission !== 'denied') {
                            Notification.requestPermission().then(permission => {
                                console.log('🔔 [DEBUG] Permission result:', permission);
                                if (permission === 'granted') {
                                    // Retry the notification
                                    this.triggerChatDesktopNotification(chatData);
                                }
                            });
                        }
                    }
                }
            },            // Helper method to get a clean message preview
            getMessagePreview(message) {
                if (!message) return 'New message';

                // Handle different message types
                if (message.type === 'image') return '📷 Image';
                if (message.type === 'document') return '📄 Document';
                if (message.type === 'audio') return '🎵 Audio';
                if (message.type === 'video') return '🎬 Video';
                if (message.type === 'location') return '📍 Location';
                if (message.type === 'sticker') return '😊 Sticker';

                // For text messages, clean and truncate
                let text = message.message || message.body || 'New message';

                // Remove HTML tags if present
                text = text.replace(/<[^>]*>/g, '');

                // Remove excessive whitespace
                text = text.replace(/\s+/g, ' ').trim();

                // Truncate long messages
                if (text.length > 50) {
                    text = text.substring(0, 50) + '...';
                }

                return text || 'New message';
            },
        }
    }
</script>
