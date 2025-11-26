<?php

namespace App\Providers;

use App\Models\Page;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PageRouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::middleware('web')->group(function () { // Ensure web middleware is applied
            // Single catch-all route for all page URLs
            Route::get('/pages/{any?}', function ($any = null) {
                if (empty($any)) {
                    return $this->handleHomePage();
                }

                // Get all page slugs
                $allPages = Page::select('id', 'title', 'description', 'slug', 'parent_id', 'status')
                    ->where('status', true)
                    ->get();

                // Current URL path
                $currentPath = '/pages/'.$any;
                // Find matching page based on full path
                foreach ($allPages as $page) {
                    if ($this->buildPagePath($page, $allPages) === $currentPath) {
                        return view('pages.show', compact('page'));
                    }
                }
                abort(404);
            })->where('any', '^(?).*$');
        });
    }

    protected function buildPagePath($page, $allPages)
    {
        $path = $page->slug;
        $currentPage = $page;

        while ($currentPage->parent_id !== null) {
            $parentPage = $allPages->firstWhere('id', $currentPage->parent_id);
            if ($parentPage) {
                $path = $parentPage->slug.'/'.$path;
                $currentPage = $parentPage;
            } else {
                break;
            }
        }

        return '/pages/'.$path;
    }

    protected function handleHomePage()
    {
        $page = Page::where('slug', 'home')
            ->where('status', true)
            ->first();

        if ($page) {
            return view('pages.show', compact('page'));
        }

        return view('welcome');
    }
}
