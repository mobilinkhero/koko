<?php

namespace App\Http\Controllers;

use App\Models\Theme;

class HomeController extends Controller
{
    public function landingPage()
    {
        // Redirect directly to login page - landing page disabled
        if (auth()->check()) {
            // If user is already logged in, redirect to appropriate dashboard
            if (tenant_check()) {
                return redirect()->to(tenant_route('tenant.dashboard'));
            } else {
                return redirect()->route('admin.dashboard');
            }
        }
        
        // Redirect to login page
        return redirect()->route('login');
    }
}
