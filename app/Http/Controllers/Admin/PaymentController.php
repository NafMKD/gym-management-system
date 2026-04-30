<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\InteractsWithReportExports;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRepository;
use App\Support\DataTables\UserNameSearch;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    use InteractsWithReportExports;

    public function __construct(
        protected PaymentRepository $paymentRepository,
        protected InvoiceRepository $invoiceRepository
    ) {
    }

    /**
     * Display a listing of the payments.
     */
    public function index(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'payments.list', [
                'creators' => $this->getFinancialCreators(),
                'exportColumns' => $this->paymentExportColumns(),
                'defaultExportColumns' => $this->defaultPaymentColumns(),
            ]);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Display a specific payment.
     */
    public function show(Payment $payment): View|RedirectResponse
    {
        try {
            $payment->load(['invoice.customer', 'invoice.createdBy', 'membership.user', 'createdBy']);

            return view(self::ADMIN_.'payments.view', compact('payment'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Invoice $invoice): View|RedirectResponse
    {
        try {
            if ($this->invoiceRepository->isInvoicePaid($invoice)) {
                return redirect()->route('admin.payments.list')->with(self::ERROR_, __('This invoice is already paid'));
            }
            return view(self::ADMIN_.'payments.add', compact('invoice'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Store a new payment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => [
                'required',
                'exists:invoices,id',
                function ($attribute, $value, $fail) {
                    $invoice = Invoice::find($value);
                    if ($invoice && $this->invoiceRepository->isInvoicePaid($invoice)) {
                        $fail(__('The selected invoice is already paid and cannot accept further payments.'));
                    }
                },
            ],
            'membership_id' => [
                Rule::requiredIf(function () use ($request) {
                    $inv = Invoice::find($request->input('invoice_id'));

                    return $inv && ($inv->invoice_source ?? 'membership') === 'membership';
                }),
                'nullable',
                'exists:memberships,id',
                function ($attribute, $value, $fail) use ($request) {
                    $invoice = Invoice::find($request->input('invoice_id'));
                    if (! $invoice || ($invoice->invoice_source ?? 'membership') !== 'membership') {
                        return;
                    }
                    if ((string) $value !== (string) $invoice->membership_id) {
                        $fail(__('The membership does not match this invoice.'));
                    }
                },
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($request) {
                    $invoice = Invoice::find($request->input('invoice_id'));
                    if ($invoice) {
                        $remaining = (float) $invoice->amount - (float) $invoice->payments()->where('status', 'completed')->sum('amount');
                        if ((float) $value > $remaining) {
                            $fail(__('The payment amount cannot exceed the remaining amount of the invoice (maximum: :amount).', [
                                'amount' => number_format(max(0, $remaining), 2),
                            ]));
                        }
                    }
                },
            ],
            'payment_method' => 'required|in:cash,bank',
            'payment_bank' => [
                Rule::requiredIf(fn () => $request->input('payment_method') === 'bank'),
                'nullable',
                'in:telebirr,cbe,boa',
            ],
            'bank_transaction_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only([
            'invoice_id',
            'membership_id',
            'amount',
            'payment_method',
            'payment_bank',
            'bank_transaction_number',
        ]);

        try {
            $attributes['status'] = 'completed';
            if (($attributes['payment_method'] ?? '') === 'cash') {
                $attributes['payment_bank'] = null;
            }

            $this->paymentRepository->store($attributes);

            return redirect()->route('admin.payments.list')->with(self::SUCCESS_, 'Payment'.self::SUCCESS_STORE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Record a refund against a paid (or partially paid) invoice.
     */
    public function storeRefund(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($request) {
                    $invoice = Invoice::find($request->input('invoice_id'));
                    if ($invoice) {
                        $netPaid = (float) $invoice->payments()->where('status', 'completed')->sum('amount');
                        if ((float) $value > $netPaid) {
                            $fail(__('Refund cannot exceed net paid (:max).', ['max' => number_format($netPaid, 2)]));
                        }
                    }
                },
            ],
            'payment_method' => 'required|in:cash,bank',
            'payment_bank' => [
                Rule::requiredIf(fn () => $request->input('payment_method') === 'bank'),
                'nullable',
                'in:telebirr,cbe,boa',
            ],
            'bank_transaction_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $payload = $request->only(['invoice_id', 'amount', 'payment_method', 'payment_bank', 'bank_transaction_number', 'notes']);
            if (($payload['payment_method'] ?? '') === 'cash') {
                $payload['payment_bank'] = null;
            }
            $this->paymentRepository->recordRefund($payload);

            return redirect()
                ->route('admin.invoices.view', $request->input('invoice_id'))
                ->with(self::SUCCESS_, __('Refund recorded.'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Retrieve payment data from the database.
     */
    public function getPaymentsData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->paymentFilterRules());

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Invalid input values.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $filters = $validator->validated();
        $query = $this->paymentRepository->getFilteredPaymentsQuery($filters);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('name', function (Payment $payment) {
                if (($payment->invoice?->invoice_source ?? 'membership') === 'merchandise') {
                    return $payment->invoice?->customer?->getName() ?? 'N/A';
                }

                return $payment->membership?->user?->getName() ?? 'N/A';
            })
            ->addColumn('invoice', fn (Payment $payment) => $payment->invoice?->invoice_number ?? 'N/A')
            ->addColumn('source', fn (Payment $payment) => ucfirst((string) ($payment->invoice?->invoice_source ?? 'membership')))
            ->editColumn('amount', fn (Payment $payment) => number_format((float) $payment->amount, 2))
            ->editColumn('payment_type', fn (Payment $payment) => ($payment->payment_type ?? 'payment') === 'refund' ? __('Refund') : __('Payment'))
            ->editColumn('payment_method', fn (Payment $payment) => ucfirst((string) $payment->payment_method))
            ->editColumn('payment_bank', fn (Payment $payment) => $payment->payment_bank ? strtoupper((string) $payment->payment_bank) : '-')
            ->editColumn('payment_date', fn (Payment $payment) => $this->formatDateTime($payment->payment_date))
            ->addColumn('created_by', fn (Payment $payment) => $payment->createdBy?->getName() ?? __('Legacy / Unknown'))
            ->editColumn('status', function (Payment $payment) {
                $badgeClass = match ($payment->status) {
                    'completed' => 'badge-success',
                    'pending' => 'badge-warning',
                    'failed' => 'badge-danger',
                    default => 'badge-secondary',
                };

                return '<span class="badge '.$badgeClass.'">'.ucwords((string) $payment->status).'</span>';
            })
            ->addColumn('action', function (Payment $payment) {
                return '
                    <a href="' . route('admin.payments.view', $payment->id) . '" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> '.__('View').'
                    </a>
                ';
            })
            ->filterColumn('name', function ($query, $keyword) {
                UserNameSearch::applyForPaymentPersonName($query, $keyword);
            })
            ->filterColumn('invoice', function ($query, $keyword) {
                $query->whereHas('invoice', function ($invoiceQuery) use ($keyword) {
                    $invoiceQuery->where('invoice_number', 'like', '%'.UserNameSearch::escapeLike($keyword).'%');
                });
            })
            ->filterColumn('source', function ($query, $keyword) {
                $query->whereHas('invoice', function ($invoiceQuery) use ($keyword) {
                    $invoiceQuery->where('invoice_source', 'like', '%'.UserNameSearch::escapeLike($keyword).'%');
                });
            })
            ->filterColumn('created_by', function ($query, $keyword) {
                $query->whereHas('createdBy', function ($creatorQuery) use ($keyword) {
                    UserNameSearch::applyToUserQuery($creatorQuery, $keyword);
                });
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    /**
     * Mark a payment as failed.
     */
    public function markFailed(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|exists:payments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Invalid payment ID.'),
            ], 400);
        }

        try {
            $payment = Payment::find($request->input('payment_id'));
            $this->paymentRepository->makeAsFailed($payment);

            return response()->json([
                'message' => __('Payment has been marked as failed.'),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => __('An error occurred while marking the payment as failed.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark a payment as completed.
     */
    public function markCompleted(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|exists:payments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Invalid payment ID.'),
            ], 400);
        }

        try {
            $payment = Payment::find($request->input('payment_id'));
            $this->paymentRepository->makeAsComplete($payment);

            return response()->json([
                'message' => __('Payment has been marked as completed.'),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => __('An error occurred while marking the payment as completed.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the revenue overview with filter capabilities using DataTables.
     */
    public function revenueOverview(Request $request): View|RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), $this->paymentFilterRules());

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => __('Invalid input values.'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            if ($request->ajax()) {
                $filters = $validator->validated();
                $query = $this->paymentRepository->getFilteredPaymentsQuery($filters);

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('name', function (Payment $payment) {
                        if (($payment->invoice?->invoice_source ?? 'membership') === 'merchandise') {
                            return $payment->invoice?->customer?->getName() ?? 'N/A';
                        }

                        return $payment->membership?->user?->getName() ?? 'N/A';
                    })
                    ->addColumn('invoice', fn (Payment $payment) => $payment->invoice?->invoice_number ?? 'N/A')
                    ->addColumn('source', fn (Payment $payment) => ucfirst((string) ($payment->invoice?->invoice_source ?? 'membership')))
                    ->editColumn('amount', fn (Payment $payment) => number_format((float) $payment->amount, 2))
                    ->editColumn('payment_type', fn (Payment $payment) => ($payment->payment_type ?? 'payment') === 'refund' ? __('Refund') : __('Payment'))
                    ->editColumn('payment_method', fn (Payment $payment) => ucfirst((string) $payment->payment_method))
                    ->editColumn('payment_bank', fn (Payment $payment) => $payment->payment_bank ? strtoupper((string) $payment->payment_bank) : '-')
                    ->editColumn('payment_date', fn (Payment $payment) => $this->formatDateTime($payment->payment_date))
                    ->addColumn('created_by', fn (Payment $payment) => $payment->createdBy?->getName() ?? __('Legacy / Unknown'))
                    ->editColumn('status', function (Payment $payment) {
                        $badgeClass = match ($payment->status) {
                            'completed' => 'badge-success',
                            'pending' => 'badge-warning',
                            'failed' => 'badge-danger',
                            default => 'badge-secondary',
                        };

                        return '<span class="badge '.$badgeClass.'">'.ucwords((string) $payment->status).'</span>';
                    })
                    ->filterColumn('name', function ($query, $keyword) {
                        UserNameSearch::applyForPaymentPersonName($query, $keyword);
                    })
                    ->filterColumn('invoice', function ($query, $keyword) {
                        $query->whereHas('invoice', function ($invoiceQuery) use ($keyword) {
                            $invoiceQuery->where('invoice_number', 'like', '%'.UserNameSearch::escapeLike($keyword).'%');
                        });
                    })
                    ->filterColumn('source', function ($query, $keyword) {
                        $query->whereHas('invoice', function ($invoiceQuery) use ($keyword) {
                            $invoiceQuery->where('invoice_source', 'like', '%'.UserNameSearch::escapeLike($keyword).'%');
                        });
                    })
                    ->filterColumn('created_by', function ($query, $keyword) {
                        $query->whereHas('createdBy', function ($creatorQuery) use ($keyword) {
                            UserNameSearch::applyToUserQuery($creatorQuery, $keyword);
                        });
                    })
                    ->rawColumns(['status'])
                    ->make(true);
            }

            return view(self::ADMIN_.'payments.revenue', [
                'creators' => $this->getFinancialCreators(),
                'exportColumns' => $this->paymentExportColumns(),
                'defaultExportColumns' => $this->defaultPaymentColumns(),
            ]);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Get the total revenue and count of transactions.
     */
    public function getTotalRevenue(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->paymentFilterRules());

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Invalid input values.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $filters = $validator->validated();
            $query = $this->paymentRepository->getFilteredPaymentsQuery($filters);

            $totalRevenue = (float) (clone $query)->sum('amount');
            $totalTransactions = (int) (clone $query)->count();
            $netPayments = (int) (clone $query)->where('payment_type', 'payment')->count();
            $refundTransactions = (int) (clone $query)->where('payment_type', 'refund')->count();

            return response()->json([
                'totalRevenue' => number_format($totalRevenue, 2, '.', ''),
                'totalTransactions' => $totalTransactions,
                'netPayments' => $netPayments,
                'refundTransactions' => $refundTransactions,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error fetching revenue data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export filtered payments as CSV.
     */
    public function exportPaymentsCsv(Request $request): StreamedResponse|RedirectResponse
    {
        return $this->exportPaymentReport(
            $request,
            'payments-report',
            __('Payments Report'),
            route('admin.payments.list')
        );
    }

    /**
     * Print-friendly payments report.
     */
    public function printPaymentsReport(Request $request): View|RedirectResponse
    {
        return $this->printPaymentReport(
            $request,
            __('Payments Report'),
            __('Detailed payment transactions with active filters.'),
            route('admin.payments.list')
        );
    }

    /**
     * Export filtered revenue results as CSV.
     */
    public function exportRevenueCsv(Request $request): StreamedResponse|RedirectResponse
    {
        return $this->exportPaymentReport(
            $request,
            'revenue-report',
            __('Revenue Report'),
            route('admin.payments.revenue.list')
        );
    }

    /**
     * Print-friendly revenue report.
     */
    public function printRevenueReport(Request $request): View|RedirectResponse
    {
        return $this->printPaymentReport(
            $request,
            __('Revenue Report'),
            __('Revenue-focused payment view with totals and active filters.'),
            route('admin.payments.revenue.list')
        );
    }

    /**
     * @return array<string, string>
     */
    private function paymentFilterRules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'payment_method' => 'nullable|in:cash,bank',
            'payment_bank' => 'nullable|in:telebirr,cbe,boa',
            'status' => 'nullable|in:pending,completed,failed',
            'created_by_user_id' => 'nullable|integer|exists:users,id',
            'payment_type' => 'nullable|in:payment,refund',
            'invoice_source' => 'nullable|in:membership,merchandise',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function getFinancialCreators()
    {
        return User::query()
            ->whereIn('role', ['admin', 'reception', 'accountant'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * @return array<string, array{label:string,value:callable}>
     */
    private function paymentExportColumns(): array
    {
        return [
            'payment_id' => [
                'label' => 'Payment ID',
                'value' => fn (Payment $payment) => $payment->id,
            ],
            'name' => [
                'label' => 'Customer / Member',
                'value' => fn (Payment $payment) => ($payment->invoice?->invoice_source ?? 'membership') === 'merchandise'
                    ? ($payment->invoice?->customer?->getName() ?? 'N/A')
                    : ($payment->membership?->user?->getName() ?? 'N/A'),
            ],
            'invoice' => [
                'label' => 'Invoice Number',
                'value' => fn (Payment $payment) => $payment->invoice?->invoice_number ?? 'N/A',
            ],
            'source' => [
                'label' => 'Source',
                'value' => fn (Payment $payment) => ucfirst((string) ($payment->invoice?->invoice_source ?? 'membership')),
            ],
            'payment_type' => [
                'label' => 'Payment Type',
                'value' => fn (Payment $payment) => ($payment->payment_type ?? 'payment') === 'refund' ? __('Refund') : __('Payment'),
            ],
            'amount' => [
                'label' => 'Amount',
                'value' => fn (Payment $payment) => number_format((float) $payment->amount, 2, '.', ''),
            ],
            'payment_method' => [
                'label' => 'Payment Method',
                'value' => fn (Payment $payment) => ucfirst((string) $payment->payment_method),
            ],
            'payment_bank' => [
                'label' => 'Payment Bank',
                'value' => fn (Payment $payment) => $payment->payment_bank ? strtoupper((string) $payment->payment_bank) : '-',
            ],
            'bank_transaction_number' => [
                'label' => 'Transaction Number',
                'value' => fn (Payment $payment) => $payment->bank_transaction_number ?? '-',
            ],
            'payment_date' => [
                'label' => 'Payment Date',
                'value' => fn (Payment $payment) => $this->formatDateTime($payment->payment_date, 'Y-m-d H:i:s'),
            ],
            'created_by' => [
                'label' => 'Created By',
                'value' => fn (Payment $payment) => $payment->createdBy?->getName() ?? __('Legacy / Unknown'),
            ],
            'status' => [
                'label' => 'Status',
                'value' => fn (Payment $payment) => ucfirst((string) $payment->status),
            ],
            'notes' => [
                'label' => 'Notes',
                'value' => fn (Payment $payment) => $payment->notes ?? '-',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function defaultPaymentColumns(): array
    {
        return ['payment_id', 'name', 'invoice', 'source', 'payment_type', 'amount', 'payment_method', 'payment_bank', 'payment_date', 'created_by', 'status'];
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    private function buildPaymentExportRow(Payment $payment, array $columns): array
    {
        return array_map(function ($column) use ($payment) {
            return (string) call_user_func($this->paymentExportColumns()[$column]['value'], $payment);
        }, $columns);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function paymentFilterSummary(array $filters): string
    {
        return implode(' | ', [
            __('From').': '.($filters['start_date'] ?? __('Any')),
            __('To').': '.($filters['end_date'] ?? __('Any')),
            __('Method').': '.(($filters['payment_method'] ?? null) ? ucfirst((string) $filters['payment_method']) : __('All')),
            __('Bank').': '.(($filters['payment_bank'] ?? null) ? strtoupper((string) $filters['payment_bank']) : __('All')),
            __('Status').': '.(($filters['status'] ?? null) ? ucfirst((string) $filters['status']) : __('All')),
            __('Type').': '.(($filters['payment_type'] ?? null) ? ucfirst((string) $filters['payment_type']) : __('All')),
            __('Source').': '.(($filters['invoice_source'] ?? null) ? ucfirst((string) $filters['invoice_source']) : __('All')),
            __('Created by').': '.($this->creatorName($filters['created_by_user_id'] ?? null) ?? __('All')),
        ]);
    }

    private function paymentReportTotals(array $filters): array
    {
        $query = $this->paymentRepository->getFilteredPaymentsQuery($filters);

        return [
            'totalRevenue' => (float) (clone $query)->sum('amount'),
            'totalTransactions' => (int) (clone $query)->count(),
            'paymentTransactions' => (int) (clone $query)->where('payment_type', 'payment')->count(),
            'refundTransactions' => (int) (clone $query)->where('payment_type', 'refund')->count(),
        ];
    }

    private function creatorName(mixed $creatorId): ?string
    {
        if (empty($creatorId)) {
            return null;
        }

        $creator = User::query()->find((int) $creatorId);

        return $creator?->getName();
    }

    private function formatDateTime(mixed $value, string $format = 'd/m/Y H:i'): string
    {
        if (blank($value)) {
            return '-';
        }

        return Carbon::parse($value)->setTimezone('Africa/Addis_Ababa')->format($format);
    }

    private function exportPaymentReport(Request $request, string $filenamePrefix, string $reportTitle, string $redirectRoute): StreamedResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), array_merge($this->paymentFilterRules(), [
            'columns' => 'nullable|array',
            'columns.*' => 'string|in:payment_id,name,invoice,source,payment_type,amount,payment_method,payment_bank,bank_transaction_number,payment_date,created_by,status,notes',
        ]));

        if ($validator->fails()) {
            return redirect($redirectRoute)->withErrors($validator)->withInput();
        }

        $filters = $validator->validated();
        $columns = $this->normalizeSelectedColumns(
            $filters['columns'] ?? null,
            $this->paymentExportColumns(),
            $this->defaultPaymentColumns()
        );

        try {
            $filename = $this->buildReportFilename($filenamePrefix, $filters, 'start_date', 'end_date');
            $query = $this->paymentRepository
                ->getFilteredPaymentsQuery($filters)
                ->orderByDesc('payment_date')
                ->orderByDesc('id');

            return response()->streamDownload(function () use ($query, $filters, $columns, $reportTitle) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, [__('Report'), $reportTitle]);
                fputcsv($out, [__('Generated at'), now('Africa/Addis_Ababa')->format('Y-m-d H:i:s')]);
                fputcsv($out, [__('Filters'), $this->paymentFilterSummary($filters)]);
                fputcsv($out, []);
                fputcsv($out, array_map(
                    fn ($column) => $this->paymentExportColumns()[$column]['label'],
                    $columns
                ));

                $query->chunk(200, function ($chunk) use ($out, $columns) {
                    foreach ($chunk as $payment) {
                        fputcsv($out, $this->buildPaymentExportRow($payment, $columns));
                    }
                });
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        } catch (Throwable $e) {
            return redirect($redirectRoute)->with(self::ERROR_, $e->getMessage());
        }
    }

    private function printPaymentReport(Request $request, string $reportTitle, string $reportSubtitle, string $redirectRoute): View|RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->paymentFilterRules());

        if ($validator->fails()) {
            return redirect($redirectRoute)->withErrors($validator)->withInput();
        }

        $filters = $validator->validated();

        try {
            $payments = $this->paymentRepository
                ->getFilteredPaymentsQuery($filters)
                ->orderByDesc('payment_date')
                ->orderByDesc('id')
                ->get();

            return view(self::ADMIN_.'payments.print', [
                'payments' => $payments,
                'filters' => $filters,
                'reportGeneratedAt' => now('Africa/Addis_Ababa'),
                'filterSummary' => $this->paymentFilterSummary($filters),
                'reportTitle' => $reportTitle,
                'reportSubtitle' => $reportSubtitle,
                'totals' => $this->paymentReportTotals($filters),
            ]);
        } catch (Throwable $e) {
            return redirect($redirectRoute)->with(self::ERROR_, $e->getMessage());
        }
    }
}
