<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Repositories\AccountantDashboardRepository;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected AccountantDashboardRepository $accountantDashboardRepository
    ) {
    }

    /**
     * Display accountant reporting home.
     */
    public function index(): View
    {
        return view('pages.staff.accountant-home', [
            'summary' => $this->accountantDashboardRepository->getDashboardSummary(),
        ]);
    }
}
