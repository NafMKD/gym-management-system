<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\NoUpdateNeededException;
use App\Http\Controllers\Controller;
use App\Models\TrainerProfile;
use App\Models\User;
use App\Repositories\TrainerProfileRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class TrainerProfileController extends Controller
{
    public function __construct(
        protected TrainerProfileRepository $trainerProfileRepository
    ) {
    }

    /**
     * Trainer-only extended profile (qualifications, default commission per attended session).
     *
     * @return View|RedirectResponse
     */
    public function edit(User $user): View|RedirectResponse
    {
        try {
            if ($user->role !== 'trainer') {
                abort(404);
            }

            $profile = TrainerProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['commission_per_session' => 0]
            );

            return view(self::ADMIN_.'trainer_profiles.edit', compact('user', 'profile'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * @return RedirectResponse
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== 'trainer') {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'qualifications' => 'nullable|string|max:5000',
            'specializations' => 'nullable|string|max:5000',
            'bio' => 'nullable|string|max:5000',
            'commission_per_session' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $profile = TrainerProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['commission_per_session' => 0]
        );

        $attributes = $request->only(['qualifications', 'specializations', 'bio', 'commission_per_session']);

        try {
            $this->trainerProfileRepository->update($profile, $attributes);

            return redirect()->route('admin.staffs.trainer_profile.edit', $user)->with(self::SUCCESS_, self::SUCCESS_UPDATE);
        } catch (NoUpdateNeededException $e) {
            return redirect()->back()->withInput()->with(self::SUCCESS_, self::SUCCESS_NO_UPDATE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }
}
