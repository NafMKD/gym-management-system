<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display home page
     *
     * @return View
     */
    public function index(): View
    {
        return view(self::ADMIN_.'index',[]);
    }
}
