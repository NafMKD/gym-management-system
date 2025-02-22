<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Package;
use App\Models\PrintBatch;
use App\Models\User;
use App\Repositories\InvoiceRepository;
use App\Repositories\MembershipRepository;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class MembershipController extends Controller
{
    public function __construct(
        protected MembershipRepository $membershipRepository, 
        protected InvoiceRepository $invoiceRepository
        )
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'memberships.list');
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View|RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        try {
            $availableMembers = User::where('role', 'member')
            ->whereNull('deleted_at')
            ->whereDoesntHave('memberships', function ($query) {
                $query->where('status', 'active');
            })
            ->get();
            $availablePackages = Package::all(); 
            return view(self::ADMIN_.'memberships.add', compact('availableMembers', 'availablePackages'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Store a new membership.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'package_id' => 'nullable|exists:packages,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only(['user_id', 'start_date', 'end_date', 'package_id', 'remaining_days', 'status']);

        try {
            if (isset($attributes['package_id'])) {
                $package = Package::find($attributes['package_id']);
                $attributes['remaining_days'] = $package->granted_days;
                $attributes['end_date'] = Carbon::parse($attributes['start_date'])->addDays($package->duration);
                $attributes['price'] = $package->price;
            } else {
                $startDate = Carbon::parse($attributes['start_date']);
                $endDate = Carbon::parse($attributes['end_date']);
                $attributes['remaining_days'] = $startDate->diffInDays($endDate);
                $attributes['price'] = $startDate->diffInDays($endDate) * self::CUSTOM_PACKAGE_PRICE;
                $attributes['end_date'] = Carbon::createFromFormat('m/d/Y', $attributes['end_date'])->format('Y-m-d');
            } 

            $attributes['status'] = 'active';
            $attributes['start_date'] = Carbon::createFromFormat('m/d/Y', $attributes['start_date'])->format('Y-m-d');

            $membership = $this->membershipRepository->store($attributes);

            // Create invoice for the membership
            $invoiceAttributes = [
                'membership_id' => $membership->id,
                'amount' => $attributes['price'],
            ];
            
            $invoice = $this->invoiceRepository->store($invoiceAttributes);

            return redirect()->route('admin.invoices.view', $invoice);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Membership $membership
     * @return View|RedirectResponse
     */
    public function show(Membership $membership): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'memberships.view', compact('membership'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Retrieves user data from the database.
     *
     * @return  JsonResponse
     */
    public function getMembershipsData(): JsonResponse
    {
        $query = Membership::query(); 

        return DataTables::of($query)
            ->addIndexColumn() 
            ->editColumn('name', function ($row) {
                return $row->user->getName();
            })
            ->editColumn('start_date', function ($row) {
                return $row->start_date;
            })
            ->editColumn('end_date', function ($row) {
                return $row->end_date; 
            })
            ->editColumn('remaining_days', function ($row) {
                return $row->remaining_days; 
            })
            ->editColumn('status', function ($row) {
                $badgeClass = '';
            
                switch ($row->status) {
                    case 'active':
                        $badgeClass = 'badge-success'; 
                        break;
            
                    case 'inactive':
                        $badgeClass = 'badge-warning'; 
                        break;
            
                    case 'cancelled':
                        $badgeClass = 'badge-danger'; 
                        break;
            
                    default:
                        break;
                }
            
                return '<span class="badge ' . $badgeClass . '">' . ucwords($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <a href="' . route('admin.memberships.view', $row->id) . '" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> View
                    </a>
                ';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    /**
     * Cancel a membership.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'membership_id' => 'required|exists:memberships,id',
        ]);

        $membership = Membership::find($validated['membership_id']);

        if ($membership->status === 'cancelled') {
            return response()->json(['message' => 'Membership is already cancelled.'], 400);
        }

        $membership->status = 'cancelled';
        $membership->save();

        return response()->json(['message' => 'Membership successfully cancelled.'], 200);
    }

    /**
     * Change the status of a membership (toggle between active and inactive).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request)
    {
        $validated = $request->validate([
            'membership_id' => 'required|exists:memberships,id',
        ]);

        $membership = Membership::find($validated['membership_id']);

        if ($membership->status === 'canceled') {
            return response()->json(['message' => 'Cannot toggle status for a canceled membership.'], 400);
        }

        if ($membership->status === 'inactive') {

            if ($membership->user->memberships()->where('status', 'active')->exists()) {
                return response()->json(['message' => 'User already has another active membership.'], 400);
            }

            if ($membership->remaining_days <= 0) {
                return response()->json(['message' => 'Cannot activate membership: Remaining days must be greater than 0.'], 400);
            }

            if ($membership->end_date < now()) {
                return response()->json(['message' => 'Cannot activate membership: End date has already passed.'], 400);
            }

            $membership->status = 'active';
        } elseif ($membership->status === 'active') {
            $membership->status = 'inactive';
        }

        $membership->save();

        return response()->json(['message' => 'Membership status updated successfully.'], 200);
    }

    /**
     * Print an ID card for a membership.
     *
     * @param Membership $membership
     * @return mixed
     */
    public function printIdCard(Membership $membership)
    {
        try 
        {
            $lastPrint = PrintBatch::latest('id')->first();

            $index = $lastPrint ? ($lastPrint->position % 8) + 1 : 1;

            $print = PrintBatch::create([
                'position' => $index,
                'membership_id' => $membership->id,
                'is_printed' => true,
            ]);

            return view(Self::ADMIN_ . 'memberships.id_card', compact('membership', 'print'));
        } catch (Throwable $e) {
            dd($e);
            return redirect()->back()->with(self::ERROR_, $e->getMessage());
        }
    }


}
