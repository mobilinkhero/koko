@extends('laravel-emails::backend.layout')

@section('title', 'Email Logs')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center p-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">Email Logs</h1>
        <div class="flex space-x-2">
            <button type="button" class="px-4 py-2 bg-danger-600 text-white rounded hover:bg-danger-700 transition"
                onclick="document.getElementById('clearModal').classList.remove('hidden')">
                Clear Logs
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-success-100 border-l-4 border-success-500 text-success-700 p-4 mb-4" role="alert">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-danger-100 border-l-4 border-danger-500 text-danger-700 p-4 mb-4" role="alert">
        {{ session('error') }}
    </div>
    @endif

    <div class="p-4 bg-gray-50 border-b">
        <div class="flex flex-wrap mb-4 -mx-2">
            <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
                <div class="bg-white p-4 rounded border border-gray-200 shadow-sm text-center">
                    <div class="text-3xl font-bold text-gray-700">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-500">Total Emails</div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
                <div class="bg-white p-4 rounded border border-gray-200 shadow-sm text-center">
                    <div class="text-3xl font-bold text-success-600">{{ $stats['sent'] }}</div>
                    <div class="text-sm text-gray-500">Sent</div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
                <div class="bg-white p-4 rounded border border-gray-200 shadow-sm text-center">
                    <div class="text-3xl font-bold text-danger-600">{{ $stats['failed'] }}</div>
                    <div class="text-sm text-gray-500">Failed</div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
                <div class="bg-white p-4 rounded border border-gray-200 shadow-sm text-center">
                    <div class="text-3xl font-bold text-info-600">{{ $stats['scheduled'] }}</div>
                    <div class="text-sm text-gray-500">Scheduled</div>
                </div>
            </div>
        </div>

        <form action="{{ route('laravel-emails.logs.index') }}" method="GET" class="mt-4">
            <div class="flex flex-wrap -mx-2">
                <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50"
                        placeholder="Subject, To, From...">
                </div>
                <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                        <option value="sent" {{ request('status')=='sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status')=='failed' ? 'selected' : '' }}>Failed</option>
                        <option value="scheduled" {{ request('status')=='scheduled' ? 'selected' : '' }}>Scheduled
                        </option>
                    </select>
                </div>
                <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50">
                </div>
                <div class="w-full md:w-1/4 px-2 mb-4 md:mb-0">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50">
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-info-600 text-white rounded hover:bg-info-700 transition">
                    Filter Results
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Template
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Subject
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Recipient
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $log->created_at->format('M d, Y H:i:s') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($log->template)
                        <a href="{{ route('laravel-emails.templates.show', $log->template) }}"
                            class="text-info-600 hover:underline">
                            {{ $log->template->name }}
                        </a>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 truncate max-w-xs">{{ $log->subject }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $log->to }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($log->status == 'sent')
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-success-100 text-success-800">
                            Sent
                        </span>
                        @elseif($log->status == 'failed')
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-danger-100 text-danger-800">
                            Failed
                        </span>
                        @elseif($log->status == 'scheduled')
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-info-100 text-info-800">
                            Scheduled
                        </span>
                        @else
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            Pending
                        </span>
                        @endif

                        @if($log->is_test)
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 ml-1">
                            Test
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('laravel-emails.logs.show', $log) }}"
                                class="text-info-600 hover:text-info-900">View</a>

                            <form action="{{ route('laravel-emails.logs.destroy', $log) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this log?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-danger-600 hover:text-danger-900">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No email logs found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">
        {{ $logs->links() }}
    </div>
</div>

<!-- Clear Logs Modal -->
<div id="clearModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Clear Email Logs</h3>
        </div>
        <form action="{{ route('laravel-emails.logs.clear') }}" method="POST">
            @csrf
            <div class="p-6">
                <p class="text-gray-700 mb-4">Select which logs you want to clear. This action cannot be undone.</p>

                <div class="mb-4">
                    <label for="clear_days" class="block text-sm font-medium text-gray-700 mb-1">Clear logs older
                        than</label>
                    <select name="days" id="clear_days"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50">
                        <option value="">All time</option>
                        <option value="1">1 day</option>
                        <option value="7">7 days</option>
                        <option value="30" selected>30 days</option>
                        <option value="90">90 days</option>
                        <option value="180">180 days</option>
                        <option value="365">1 year</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="clear_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="clear_status"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50">
                        <option value="">All statuses</option>
                        <option value="sent">Sent</option>
                        <option value="failed">Failed</option>
                        <option value="pending">Pending</option>
                        <option value="scheduled">Scheduled</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition"
                    onclick="document.getElementById('clearModal').classList.add('hidden')">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-danger-600 text-white rounded hover:bg-danger-700 transition">
                    Clear Logs
                </button>
            </div>
        </form>
    </div>
</div>
@endsection