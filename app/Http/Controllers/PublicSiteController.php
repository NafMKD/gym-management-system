<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'packages' => Package::query()
                ->orderBy('duration')
                ->orderBy('granted_days')
                ->get(),
            'featuredTrainers' => $this->trainerQuery()
                ->take(3)
                ->get(),
        ]);
    }

    public function gallery(): View
    {
        return view('public.gallery');
    }

    public function trainers(): View
    {
        return view('public.trainers', [
            'trainers' => $this->trainerQuery()->get(),
        ]);
    }

    protected function trainerQuery(): Builder
    {
        return User::query()
            ->where('role', 'trainer')
            ->with('trainerProfile')
            ->orderBy('first_name')
            ->orderBy('last_name');
    }
}
