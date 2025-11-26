<?php

namespace Modules\LogViewer\Http\Controllers;

use App\Http\Controllers\Controller;

class LogViewerController extends Controller
{
    /**
     * Display the module welcome screen
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('LogViewer::index');
    }
}
