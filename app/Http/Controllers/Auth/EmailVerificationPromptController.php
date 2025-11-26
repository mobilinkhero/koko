<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            $route = $user->user_type === 'admin'
                ? route('admin.dashboard', absolute: false)
                : tenant_route('tenant.dashboard');

            return redirect()->intended($route);
        }

        return view('auth.verify-email');
    }
}
