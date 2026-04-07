<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display trainer home.
     *
     * @return View
     */
    public function index(): View
    {
        return view(self::TRAINER.'home', [
            'portalTitle' => __('Trainer'),
        ]);
    }
}
