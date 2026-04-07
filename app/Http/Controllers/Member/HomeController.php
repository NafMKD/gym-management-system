<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display member home.
     *
     * @return View
     */
    public function index(): View
    {
        return view(self::MEMBER_.'home', [
            'portalTitle' => __('Member'),
        ]);
    }
}
