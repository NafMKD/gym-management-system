<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\MembershipExtensionRequest;
use App\Repositories\MembershipExtensionRequestRepository;
use App\Support\DataTables\UserNameSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class MembershipExtensionRequestController extends Controller
{
    public function __construct(
        protected MembershipExtensionRequestRepository $extensionRequestRepository
    ) {
    }

    /**
     * Display a listing of extension requests.
     *
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'membership_extension_requests.list');
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Show the form for creating a new extension request.
     *
     * @return View|RedirectResponse
     */
    public function create(Request $request): View|RedirectResponse
    {
        try {
            $memberships = Membership::query()
                ->where('status', 'active')
                ->with('user')
                ->orderBy('id', 'desc')
                ->get();

            $prefillMembershipId = $request->query('membership_id');

            return view(self::ADMIN_.'membership_extension_requests.add', compact(
                'memberships',
                'prefillMembershipId'
            ));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Store a newly created extension request.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'membership_id' => 'required|exists:memberships,id',
            'reason' => 'required|string|max:2000',
            'requested_days' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $this->extensionRequestRepository->store([
                'membership_id' => $request->input('membership_id'),
                'requested_by' => Auth::id(),
                'reason' => $request->input('reason'),
                'requested_days' => $request->input('requested_days'),
            ]);

            return redirect()->route('admin.memberships.extension_requests.list')->with(self::SUCCESS_, __('Extension request submitted.'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Display the specified extension request.
     *
     * @param MembershipExtensionRequest $membership_extension_request
     * @return View|RedirectResponse
     */
    public function show(MembershipExtensionRequest $membership_extension_request): View|RedirectResponse
    {
        try {
            $membership_extension_request->load(['membership.user', 'requester', 'approver']);

            return view(self::ADMIN_.'membership_extension_requests.view', [
                'extensionRequest' => $membership_extension_request,
            ]);
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Approve a pending extension request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function approve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'membership_extension_request_id' => 'required|exists:membership_extension_requests,id',
        ]);

        try {
            $extensionRequest = MembershipExtensionRequest::findOrFail($validated['membership_extension_request_id']);
            $this->extensionRequestRepository->approve($extensionRequest, (int) Auth::id());

            return response()->json(['message' => __('Extension approved and membership updated.')], 200);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Reject a pending extension request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'membership_extension_request_id' => 'required|exists:membership_extension_requests,id',
            'rejection_reason' => 'nullable|string|max:2000',
        ]);

        try {
            $extensionRequest = MembershipExtensionRequest::findOrFail($validated['membership_extension_request_id']);
            $this->extensionRequestRepository->reject(
                $extensionRequest,
                (int) Auth::id(),
                $validated['rejection_reason'] ?? null
            );

            return response()->json(['message' => __('Extension request rejected.')], 200);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Data for DataTables.
     *
     * @return JsonResponse
     */
    public function getListData(): JsonResponse
    {
        $query = MembershipExtensionRequest::query()->with(['membership.user']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('member', function ($row) {
                return $row->membership?->user?->getName() ?? 'N/A';
            })
            ->editColumn('membership_id', function ($row) {
                return (string) $row->membership_id;
            })
            ->editColumn('requested_days', function ($row) {
                return (string) $row->requested_days;
            })
            ->editColumn('reason', function ($row) {
                return \Illuminate\Support\Str::limit((string) $row->reason, 80);
            })
            ->editColumn('status', function ($row) {
                $badgeClass = match ($row->status) {
                    'approved' => 'badge-success',
                    'rejected' => 'badge-danger',
                    default => 'badge-warning',
                };

                return '<span class="badge '.$badgeClass.'">'.ucwords($row->status).'</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at?->format('d/m/Y H:i') ?? '';
            })
            ->addColumn('action', function ($row) {
                $view = '<a href="'.route('admin.memberships.extension_requests.view', $row->id).'" class="btn btn-info btn-xs btn-flat"><i class="fas fa-eye"></i> '.__('View').'</a> ';
                if ($row->status === 'pending') {
                    $view .= '<button type="button" class="btn btn-success btn-xs btn-flat btn-approve-ext" data-id="'.$row->id.'"><i class="fas fa-check"></i> '.__('Approve').'</button> ';
                    $view .= '<button type="button" class="btn btn-danger btn-xs btn-flat btn-reject-ext" data-id="'.$row->id.'"><i class="fas fa-times"></i> '.__('Reject').'</button>';
                }

                return $view;
            })
            ->filterColumn('member', function ($query, $keyword) {
                $query->whereHas('membership.user', function ($q) use ($keyword) {
                    UserNameSearch::applyToUserQuery($q, $keyword);
                });
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}
