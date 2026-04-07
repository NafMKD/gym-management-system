<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Repositories\TrainerDashboardRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected TrainerDashboardRepository $trainerDashboardRepository
    ) {
    }

    /**
     * Display trainer home.
     */
    public function index(): View
    {
        $userId = (int) Auth::id();
        $upcomingSessions = $this->trainerDashboardRepository->upcomingSessionsForTrainer($userId);
        $commissionSnapshot = $this->trainerDashboardRepository->commissionSnapshotForTrainer($userId);

        return view('pages.staff.trainer-home', [
            'portalTitle' => __('Trainer'),
            'upcomingSessions' => $upcomingSessions,
            'commissionSnapshot' => $commissionSnapshot,
        ]);
    }
}
