<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Repositories\ReceptionDeskRepository;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected ReceptionDeskRepository $receptionDeskRepository
    ) {
    }

    /**
     * Display reception (front desk) home.
     */
    public function index(): View
    {
        $todaysBookings = $this->receptionDeskRepository->todaysBookings();

        return view('pages.staff.reception-home', [
            'portalTitle' => __('Front desk'),
            'todaysBookings' => $todaysBookings,
        ]);
    }
}
