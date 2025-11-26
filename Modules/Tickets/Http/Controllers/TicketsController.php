<?php

namespace Modules\Tickets\Http\Controllers;

use App\Http\Controllers\Controller;

class TicketsController extends Controller
{
    /**
     * Display the module welcome screen
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('Tickets::index');
    }
}
