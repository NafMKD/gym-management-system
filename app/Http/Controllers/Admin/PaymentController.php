<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentRepository $paymentRepository,
        protected InvoiceRepository $invoiceRepository
        )
    {
    }
    /**
     * Display a listing of the payments.
     * 
     * @return View
     */
    public function index(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'payments.list');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Display a specific payment.
     * 
     * @param Payment $payment
     * @return View
     */
    public function show(Payment $payment): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'payments.view', compact('payment'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Invoice $invoice
     * @return View|RedirectResponse
     */
    public function create(Invoice $invoice): View|RedirectResponse
    {
        try {
            if ($invoice->status !== 'unpaid') {
                return redirect()->route('admin.payments.list')->with(self::ERROR_, __('This invoice is already paid'));
            }
            return view(self::ADMIN_.'payments.add', compact('invoice'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Store a new payment 
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => [
                'required',
                'exists:invoices,id',
                function ($attribute, $value, $fail) {
                    $invoice = Invoice::find($value);
                    if ($invoice && $invoice->status === 'paid') {
                        $fail(__("The selected invoice is already paid and cannot accept further payments."));
                    }
                },
            ],
            'membership_id' => 'required|exists:memberships,id',
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($request) {
                    $invoice = Invoice::find($request->input('invoice_id'));
                    if ($invoice && $value > $invoice->amount - $invoice->payments()->where('status', 'completed')->sum('amount')) {
                        $fail(__("The payment amount cannot exceed the remaining amount of the invoice (maximum: :amount).", [
                            'amount' => $invoice->amount - $invoice->payments()->where('status', 'completed')->sum('amount'),
                        ]));
                    }
                },
            ],
            'payment_method' => 'required|in:cash,bank'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only([
            'invoice_id',
            'membership_id',
            'amount',
            'payment_method',
        ]);

        try {
            $attributes['status'] = 'completed';

            // Store the payment
            $this->paymentRepository->store($attributes);

            // Update the invoice's status
            $invoice = Invoice::find($attributes['invoice_id']);
            if ($this->invoiceRepository->isInvoicePaid($invoice)) {
                $this->invoiceRepository->markAsPaid($invoice);
            }

            return redirect()->route('admin.payments.list')->with(self::SUCCESS_, 'Payment'.self::SUCCESS_STORE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }



    /**
     * Retrieves user data from the database.
     *
     * @return  JsonResponse
     */
    public function getPaymentsData(): JsonResponse
    {
        $query = Payment::query(); 

        return DataTables::of($query)
            ->addIndexColumn() 
            ->editColumn('name', function ($row) {
                return $row->membership->user->getName();
            })
            ->editColumn('invoice', function ($row) {
                return $row->invoice->invoice_number;
            })
            ->editColumn('amount', function ($row) {
                return number_format($row->amount, 2); 
            })
            ->editColumn('status', function ($row) {
                $badgeClass = '';
            
                switch ($row->status) {
                    case 'completed':
                        $badgeClass = 'badge-success'; 
                        break;
            
                    case 'pending':
                        $badgeClass = 'badge-warning'; 
                        break;
            
                    case 'failed':
                        $badgeClass = 'badge-danger'; 
                        break;
                    default:
                        break;
                }
            
                return '<span class="badge ' . $badgeClass . '">' . ucwords($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <a href="' . route('admin.payments.view', $row->id) . '" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> View
                    </a>
                ';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    /**
     * Mark a payment as failed.
     *
     * @param Request $request
     * 
     * @return JsonResponse
     */
    public function markFailed(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|exists:payments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __("Invalid payment ID."),
            ], 400);
        }

        try {
            $payment = Payment::find($request->input('payment_id'));
            $this->paymentRepository->makeAsFailed($payment);

            return response()->json([
                'message' => __("Payment has been marked as failed."),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => __("An error occurred while marking the payment as failed."),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark a payment as completed.
     *
     * @param Request $request
     * 
     * @return JsonResponse
     */
    public function markCompleted(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|exists:payments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __("Invalid payment ID."),
            ], 400);
        }
        
        try {
            $payment = Payment::find($request->input('payment_id'));
            $this->paymentRepository->makeAsComplete($payment);

            return response()->json([
                'message' => __("Payment has been marked as completed."),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => __("An error occurred while marking the payment as completed."),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the revenue overview with filter capabilities using DataTables.
     *
     * @param Request $request
     * @return View|RedirectResponse|JsonResponse
     */
    public function revenueOverview(Request $request): View|RedirectResponse|JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'payment_method' => 'nullable|in:cash,bank',
                'status' => 'nullable|in:pending,completed,failed',
            ]);
    
            if ($validator->fails()) {
                if ($request->ajax()) return response()->json([
                    'message' => __("Invalid input values."),
                    'errors' => $validator->errors()
                ], 400);
    
                return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
            }
    
            if ($request->ajax()) {
                $filters = $request->only(['start_date', 'end_date', 'payment_method', 'status']);
                $query = $this->paymentRepository->getFilteredPaymentsQuery($filters);
    
                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('membership_id', function ($row) {
                        return $row->membership->user->getName();
                    })
                    ->editColumn('invoice', function ($row) {
                        return $row->invoice->invoice_number;
                    })
                    ->editColumn('amount', function ($row) {
                        return number_format($row->amount, 2);
                    })
                    ->editColumn('payment_method', function ($row) {
                        return ucfirst($row->payment_method);
                    })
                    ->editColumn('payment_date', function ($row) {
                        return \Carbon\Carbon::parse($row->payment_date)->format('d/m/Y');
                    })
                    ->editColumn('status', function ($row) {
                        $badgeClass = match ($row->status) {
                            'completed' => 'badge-success',
                            'pending' => 'badge-warning',
                            'failed' => 'badge-danger',
                            default => '',
                        };
                        return '<span class="badge ' . $badgeClass . '">' . ucfirst($row->status) . '</span>';
                    })
                    ->rawColumns(['status'])
                    ->make(true);
            }
    
            return view(self::ADMIN_ . 'payments.revenue');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Get the total revenue and count of transactions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTotalRevenue(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['start_date', 'end_date', 'payment_method', 'status']);
            $query = $this->paymentRepository->getFilteredPaymentsQuery($filters);

            // Calculate total revenue and count of transactions
            $totalRevenue = $query->sum('amount');
            $totalTransactions = $query->count();

            return response()->json([
                'totalRevenue' => number_format($totalRevenue, 2),
                'totalTransactions' => $totalTransactions
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error fetching revenue data',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
