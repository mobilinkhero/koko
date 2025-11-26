<?php

namespace Modules\CacheManager\Http\Controllers;

use App\Http\Controllers\Controller;

class CacheManagerController extends Controller
{
    /**
     * Display the module welcome screen
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('CacheManager::index');
    }
}
