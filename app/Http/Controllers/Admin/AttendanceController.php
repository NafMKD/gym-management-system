<?php 

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Membership;
use App\Http\Controllers\Controller;
use \Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display the ID scan page.
     *
     * @return \Illuminate\View\View
     */
    public function showScanPage()
    {
        return view(self::ADMIN_.'attendance.scan');
    }

    /**
     * Process the scanned QR code and record attendance.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function recordAttendance(Request $request)
    {
        try {
            $validated = $request->validate([
                'qr_code' => 'required|string',
            ]);
    
            $membershipId = $validated['qr_code'];
    
            $membership = Membership::find($membershipId);
    
            if (!$membership) {
                return response()->json(['success' => false, 'message' => 'Invalid membership ID.'], 404);
            }
    
            if ($membership['status'] !== 'active') {
                return response()->json(['success' => false, 'message' => 'Membership is not active.'], 400);
            }
    
            if (Carbon::now()->greaterThan($membership->end_date)) {
                return response()->json(['success' => false, 'message' => 'Membership has expired.'], 400);
            }
    
            if ($membership->remaining_days <= 0) {
                return response()->json(['success' => false, 'message' => 'No remaining days on this membership.'], 400);
            }
    
            $membership->attendances()->create([
                'entry_date' => now(),
            ]);
    
            $membership->remaining_days -= 1;
            $membership->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully.',
                'membership' => $membership,
            ]);
        } catch (\Throwable $e) {
            dd($e);
        }
        
    }


}
