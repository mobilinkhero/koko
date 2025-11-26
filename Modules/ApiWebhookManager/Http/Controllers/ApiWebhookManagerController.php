<?php

namespace Modules\ApiWebhookManager\Http\Controllers;

use App\Http\Controllers\Controller;

class ApiWebhookManagerController extends Controller
{
    /**
     * Display the module welcome screen
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('ApiWebhookManager::index');
    }
}
