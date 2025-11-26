<x-app-layout>
    <x-slot name="title">{!! '$MODULE_NAME$' !!}</x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Module Header -->
            <div class="bg-white dark:bg-secondary-800 shadow-sm rounded-lg overflow-hidden mb-6">
                <div class="px-4 py-5 sm:px-6 bg-gradient-to-r from-primary-500 to-purple-600">
                    <h1 class="text-2xl font-bold text-white">{!! '$MODULE_NAME$' !!} Module</h1>
                    <p class="mt-1 max-w-2xl text-sm text-primary-100">Module successfully installed and activated</p>
                </div>
                <div class="border-t border-secondary-200 dark:border-secondary-700 px-4 py-5 sm:p-6">
                    <div class="flex items-center text-sm text-secondary-500 dark:text-secondary-300 mb-4">
                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-success-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Routes dangeristered successfully</span>
                    </div>

                    <h2 class="text-lg font-medium text-secondary-900 dark:text-white mb-3">Available Routes:</h2>

                    <!-- Routes Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-secondary-200 dark:divide-secondary-700">
                            <thead class="bg-secondary-50 dark:bg-secondary-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-secondary-500 dark:text-secondary-300 uppercase tracking-wider">Method</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-secondary-500 dark:text-secondary-300 uppercase tracking-wider">URI</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-secondary-500 dark:text-secondary-300 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-secondary-800 divide-y divide-secondary-200 dark:divide-secondary-700">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">GET</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900 dark:text-white">admin/{!! '$LOWER_NAME$' !!}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500 dark:text-secondary-300">{!! '$MODULE_NAME$' !!}\Http\Controllers\{!! '$MODULE_NAME$' !!}Controller@index</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">GET</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900 dark:text-white">admin/{!! '$LOWER_NAME$' !!}/create</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500 dark:text-secondary-300">{!! '$MODULE_NAME$' !!}\Http\Controllers\{!! '$MODULE_NAME$' !!}Controller@create</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200">POST</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900 dark:text-white">admin/{!! '$LOWER_NAME$' !!}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500 dark:text-secondary-300">{!! '$MODULE_NAME$' !!}\Http\Controllers\{!! '$MODULE_NAME$' !!}Controller@store</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">GET</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900 dark:text-white">admin/{!! '$LOWER_NAME$' !!}/{id}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500 dark:text-secondary-300">{!! '$MODULE_NAME$' !!}\Http\Controllers\{!! '$MODULE_NAME$' !!}Controller@show</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Documentation -->
                    <div class="mt-6 border-t border-secondary-200 dark:border-secondary-700 pt-4">
                        <h3 class="text-md font-medium text-secondary-900 dark:text-white mb-2">Module Information</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Module Name</dt>
                                <dd class="mt-1 text-sm text-secondary-900 dark:text-white">{!! '$MODULE_NAME$' !!}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Module Type</dt>
                                <dd class="mt-1 text-sm text-secondary-900 dark:text-white">Core</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Status</dt>
                                <dd class="mt-1 text-sm text-secondary-900 dark:text-white flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-success-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Active
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Namespace</dt>
                                <dd class="mt-1 text-sm text-secondary-900 dark:text-white">Modules\{!! '$MODULE_NAME$' !!}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
                <div class="bg-secondary-50 dark:bg-secondary-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                    <div class="text-sm text-secondary-500 dark:text-secondary-300">Use the navigation menu to access module features</div>
                    <a href="{{ url('admin/modules') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        All Modules
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
