<?php

namespace App\Http\Middleware;

use App\Services\LanguageService;
use Closure;
use Corbital\Installer\Installer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * The installer instance.
     */
    protected Installer $installer;

    public function __construct(
        private LanguageService $languageService, Installer $installer
    ) {
        $this->installer = $installer;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get language from the service
        $locale = Session::get('locale', config('app.locale'));
        App::setLocale($locale);

        return $next($request);
    }
}
