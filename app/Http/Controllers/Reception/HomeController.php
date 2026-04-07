<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display reception home.
     *
     * @return View
     */
    public function index(): View
    {
        return view(self::TRAINER.'home', [
            'portalTitle' => __('Reception'),
        ]);
    }
}
