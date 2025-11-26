<?php

use App\Http\Middleware\SanitizeInputs;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Route;
use Modules\Tickets\Http\Controllers\Admin\DepartmentsController as AdminDepartmentsController;
use Modules\Tickets\Http\Controllers\Admin\TicketsController as AdminTicketsController;
use Modules\Tickets\Http\Controllers\Client\TicketsController as ClientTicketsController;
use Modules\Tickets\Http\Controllers\TicketsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your module. These
| routes are loaded by the ServiceProvider.
|
*/

// Admin Routes
Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('tickets')->name('tickets.')->group(function () {
        // Dashboard and listing
        Route::get('/', [AdminTicketsController::class, 'index'])->name('index');
        Route::get('/create', [AdminTicketsController::class, 'create'])->name('create');
        Route::post('/store', [AdminTicketsController::class, 'store'])->name('store');
        Route::get('/{ticket}', action: [AdminTicketsController::class, 'show'])->name('show');
        Route::put('/{ticket}', [AdminTicketsController::class, 'update'])->name('update');
        Route::delete('/{ticket}', [AdminTicketsController::class, 'destroy'])->name('destroy');

        // Close/Reopen Routes
        Route::post('/{ticket}/close', [AdminTicketsController::class, 'closeTicket'])->name('close');
        Route::post('/{ticket}/reopen', [AdminTicketsController::class, 'reopenTicket'])->name('reopen');
        Route::put('/{ticket}/update-assignees', [AdminTicketsController::class, 'updateAssignees'])->name('update-assignees');

        // File downloads
        Route::get('/{ticket}/download', [AdminTicketsController::class, 'downloadAttachment'])->name('download-attachment');

        // API Routes for AJAX operations
        Route::post('/{ticket}/status', [AdminTicketsController::class, 'updateStatus'])->name('update-status');
        Route::post('/{ticket}/priority', [AdminTicketsController::class, 'updatePriority'])->name('update-priority');
        Route::post('/{ticket}/department', [AdminTicketsController::class, 'assignDepartment'])->name('assign-department');
        Route::post('/bulk-update', [AdminTicketsController::class, 'bulkUpdate'])->name('bulk-update');
        Route::get('/stats', [AdminTicketsController::class, 'getStats'])->name('stats');

        // Departments Routes
        Route::prefix('departments')->name('departments.')->group(function () {
            Route::get('/', [AdminDepartmentsController::class, 'index'])->name('index');
            Route::post('/', [AdminDepartmentsController::class, 'store'])->name('store');
            Route::put('/{department}', [AdminDepartmentsController::class, 'update'])->name('update');
            Route::delete('/{department}', [AdminDepartmentsController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-action', [AdminDepartmentsController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/export', [AdminDepartmentsController::class, 'export'])->name('export');
            Route::get('/translation-stats', [AdminDepartmentsController::class, 'translationStats'])->name('translation-stats');
        });
    });
});

Route::middleware(['auth', 'web', SanitizeInputs::class, TenantMiddleware::class, EnsureEmailIsVerified::class])->group(function () {
    Route::prefix('/{subdomain}')->as('tenant.')->group(function () {
        // Tickets
        Route::prefix('tickets')->name('tickets.')->group(function () {
            // Dashboard and listing

            Route::get('/', [ClientTicketsController::class, 'index'])->name('index');
            Route::get('/create', [ClientTicketsController::class, 'create'])->name('create');
            Route::get('/{ticket}/edit', [ClientTicketsController::class, 'edit'])->name('edit');
            Route::post('/store', [ClientTicketsController::class, 'store'])->name('store');
            Route::get('/{ticket}', [ClientTicketsController::class, 'show'])->name('show');

            // Reply functionality
            Route::post('/{ticket}/reply', [ClientTicketsController::class, 'reply'])->name('reply');

            // Ticket actions
            Route::post('/{ticket}/close', [ClientTicketsController::class, 'close'])->name('close');

            // File downloads
            Route::get('/{ticket}/download', [ClientTicketsController::class, 'downloadAttachment'])->name('download');

            // API endpoints
            Route::get('/{ticket}/api', [ClientTicketsController::class, 'apiShow'])->name('api.show');
            Route::get('/data', [ClientTicketsController::class, 'getData'])->name('data');

            // Close/Reopen Routes
            Route::post('/{ticket}/close', [ClientTicketsController::class, 'closeTicket'])->name('close');
            Route::post('/{ticket}/reopen', [ClientTicketsController::class, 'reopenTicket'])->name('reopen');
        });
    });
});

// Default module route
Route::middleware('web')->group(function () {
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketsController::class, 'index'])->name('tickets.index');
    });
});
