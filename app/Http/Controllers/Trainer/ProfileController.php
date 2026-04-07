<?php

namespace App\Http\Controllers\Trainer;

use App\Exceptions\NoUpdateNeededException;
use App\Http\Controllers\Controller;
use App\Models\TrainerProfile;
use App\Repositories\TrainerProfileRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    public function __construct(
        protected TrainerProfileRepository $trainerProfileRepository
    ) {
    }

    public function edit(): View|RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== 'trainer') {
            abort(403);
        }

        try {
            $profile = TrainerProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['commission_per_session' => 0]
            );

            return view('pages.staff.trainer-profile', compact('user', 'profile'));
        } catch (Throwable $e) {
            return redirect()->route('trainer.home')->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== 'trainer') {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'qualifications' => 'nullable|string|max:5000',
            'specializations' => 'nullable|string|max:5000',
            'bio' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $profile = TrainerProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['commission_per_session' => 0]
        );

        // Commission rate is admin-only (staff trainer profile); trainers edit bio fields only.
        $attributes = $request->only(['qualifications', 'specializations', 'bio']);

        try {
            $this->trainerProfileRepository->update($profile, $attributes);

            return redirect()->route('trainer.profile.edit')->with(self::SUCCESS_, self::SUCCESS_UPDATE);
        } catch (NoUpdateNeededException $e) {
            return redirect()->back()->withInput()->with(self::SUCCESS_, self::SUCCESS_NO_UPDATE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }
}
