<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Repositories\MemberPortalRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected MemberPortalRepository $memberPortalRepository
    ) {
    }

    /**
     * Display member home.
     */
    public function index(): View
    {
        $userId = (int) Auth::id();
        $membership = $this->memberPortalRepository->activeMembershipForUser($userId);
        $recentBookings = $this->memberPortalRepository->recentClassBookingsForUser($userId);

        return view(self::MEMBER_.'home', [
            'portalTitle' => __('Member'),
            'membership' => $membership,
            'recentBookings' => $recentBookings,
        ]);
    }
}
