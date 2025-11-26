@extends('laravel-emails::backend.layout')

@section('title', 'Email Log Details')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center p-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">Email Log Details</h1>
        <div class="flex space-x-2">
            <a href="{{ route('laravel-emails.logs.index') }}"
                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                Back to Logs
            </a>

            <form action="{{ route('laravel-emails.logs.destroy', $log) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this log?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-danger-600 text-white rounded hover:bg-danger-700 transition">
                    Delete Log
                </button>
            </form>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-800 mb-2">Log Details</h2>
                    <div class="bg-gray-50 p-4 rounded border border-gray-200">
                        <dl class="grid grid-cols-1 gap-3">
                            <div class="grid grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">ID:</dt>
                                <dd class="text-sm text-gray-900 col-span-2">{{ $log->id }}</dd>
                            </div>
                            <div class="grid grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">Status:</dt>
                                <dd class="col-span-2">
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
                                </dd>
                            </div>
                            <div class="grid grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">Created At:</dt>
                                <dd class="text-sm text-gray-900 col-span-2">{{ $log->created_at->format('M d, Y H:i:s')
                                    }}</dd>
                            </div>
                            <div class="grid grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">Sent At:</dt>
                                <dd class="text-sm text-gray-900 col-span-2">{{ $log->sent_at ? $log->sent_at->format('M
                                    d, Y H:i:s') : 'Not sent yet' }}</dd>
                            </div>
                            @if($log->scheduled_at)
                            <div class="grid grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">Scheduled For:</dt>
                                <dd class="text-sm text-gray-900 col-span-2">{{ $log->scheduled_at->format('M d, Y
                                    H:i:s') }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-800 mb-2">Email Details</h2>
                    <div class="bg-gray-50 p-4 rounded border border-gray-200">
                        <dl class="grid grid-cols-1 gap-3">
                            <div class="grid grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">Subject:</dt>
                                <dd class="text-sm text-gray-900 col-span-2">{{ $log->subject }}</dd>
                            </div>
                            <div class="grid grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">From:</dt>
                                <dd class="text-sm text-gray-900 col-span-2">{{ $log->from }}</dd>
                            </div>
                            <div class="grid grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">To:</dt>
                                <dd class="text-sm text-gray-900 col-span-2">{{ $log->to }}</dd>
                            </div>
                            @if($log->cc)
                            <div class="grid grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">CC:</dt>
                                <dd class="text-sm text-gray-900 col-span-2">{{ $log->cc }}</dd>
                            </div>
                            @endif
                            @if($log->bcc)
                            <div class="grid grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">BCC:</dt>
                                <dd class="text-sm text-gray-900 col-span-2">{{ $log->bcc }}</dd>
                            </div>
                            @endif
                            @if($log->reply_to)
                            <div class="grid grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">Reply To:</dt>
                                <dd class="text-sm text-gray-900 col-span-2">{{ $log->reply_to }}</dd>
                            </div>
                            @endif
                            <div class="grid grid-cols-3">
                                <dt class="text-sm font-medium text-gray-500">Template:</dt>
                                <dd class="text-sm text-gray-900 col-span-2">
                                    @if($log->template)
                                    <a href="{{ route('laravel-emails.templates.show', $log->template) }}"
                                        class="text-info-600 hover:underline">
                                        {{ $log->template->name }}
                                    </a>
                                    @else
                                    <span class="text-gray-500">No template (direct content)</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @if($log->error)
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-danger-800 mb-2">Error</h2>
                    <div class="bg-danger-50 p-4 rounded border border-danger-200">
                        <pre class="text-sm text-danger-700 whitespace-pre-wrap">{{ $log->error }}</pre>
                    </div>
                </div>
                @endif
            </div>

            <div>
                @if($log->data)
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-800 mb-2">Data Variables</h2>
                    <div class="bg-gray-50 p-4 rounded border border-gray-200">
                        <div class="text-sm text-gray-700 bg-gray-100 p-3 rounded overflow-x-auto">
                            <pre>{{ json_encode($log->data, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
                @endif

                @if($log->template)
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-800 mb-2">Email Preview</h2>
                    <div class="bg-white p-4 rounded border border-gray-200">
                        <div class="border-b pb-2 mb-4">
                            <h3 class="text-sm font-medium text-gray-700">Subject: {{ $log->subject }}</h3>
                        </div>
                        <div class="prose max-w-none">
                            @if($log->data && is_array($log->data))
                            {!! $log->template->renderContent($log->data) !!}
                            @else
                            {!! $log->template->content !!}
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                @if($log->status == 'failed')
                <div class="mt-8">
                    <h2 class="text-lg font-medium text-gray-800 mb-2">Retry Options</h2>
                    <div class="bg-info-50 p-4 rounded border border-info-200">
                        <p class="text-sm text-info-700 mb-4">You can attempt to resend this failed email.</p>
                        <form action="{{ route('laravel-emails.test.send') }}" method="POST">
                            @csrf
                            <input type="hidden" name="template_id" value="{{ $log->email_template_id }}">
                            <input type="hidden" name="to_email" value="{{ $log->to }}">
                            <input type="hidden" name="test_data" value="{{ json_encode($log->data) }}">
                            <button type="submit"
                                class="px-4 py-2 bg-info-600 text-white text-sm rounded hover:bg-info-700 transition">
                                Retry Sending
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection