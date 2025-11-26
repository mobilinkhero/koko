<?php

namespace Corbital\LaravelEmails\Http\Controllers;

use App\Http\Controllers\Controller;
use Corbital\LaravelEmails\Models\SimplifiedEmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    /**
     * Display a listing of email logs.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = SimplifiedEmailLog::query();

        // Apply filters
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('to', 'like', "%{$search}%")
                    ->orWhere('from', 'like', "%{$search}%");
            });
        }

        if ($startDate = $request->get('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->get('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Get logs with pagination
        $logs = $query->with('template')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Get stats
        $stats = [
            'total' => SimplifiedEmailLog::count(),
            'sent' => SimplifiedEmailLog::where('status', 'sent')->count(),
            'failed' => SimplifiedEmailLog::where('status', 'failed')->count(),
            'pending' => SimplifiedEmailLog::where('status', 'pending')->count(),
            'scheduled' => SimplifiedEmailLog::where('status', 'scheduled')->count(),
        ];

        return view('laravel-emails::logs.index', compact('logs', 'stats'));
    }

    /**
     * Display the specified email log.
     *
     * @return \Illuminate\View\View
     */
    public function show(SimplifiedEmailLog $log)
    {
        return view('laravel-emails::logs.show', compact('log'));
    }

    /**
     * Remove the specified email log.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(SimplifiedEmailLog $log)
    {
        $log->delete();

        return redirect()->route('laravel-emails.logs.index')
            ->with('success', 'Email log deleted successfully');
    }

    /**
     * Clear all or filtered email logs.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear(Request $request)
    {
        $request->validate([
            'days' => 'nullable|integer|min=1',
            'status' => 'nullable|string|in:sent,failed,pending,scheduled',
        ]);

        $query = SimplifiedEmailLog::query();

        // Apply filters
        if ($days = $request->get('days')) {
            $query->where('created_at', '<', now()->subDays($days));
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Get count before deletion
        $count = $query->count();

        // Delete records
        $query->delete();

        return redirect()->route('laravel-emails.logs.index')
            ->with('success', "{$count} email logs cleared successfully");
    }
}
