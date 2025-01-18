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
            'payment_date' => 'required|date|before_or_equal:now',
            'payment_method' => 'required|in:cash,bank',
            'payment_bank' => 'nullable|required_if:payment_method,bank|in:telebirr,cbe,boa',
            'bank_transaction_number' => 'nullable|required_if:payment_method,bank|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only([
            'invoice_id',
            'membership_id',
            'amount',
            'payment_date',
            'payment_method',
            'payment_bank',
            'bank_transaction_number',
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
}
