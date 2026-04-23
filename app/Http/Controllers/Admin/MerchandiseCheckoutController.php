<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Repositories\MerchandiseRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class MerchandiseCheckoutController extends Controller
{
    public function __construct(
        protected MerchandiseRepository $merchandiseRepository
    ) {
    }

    /**
     * @return View|RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        try {
            $products = Product::query()->where('is_active', true)->orderBy('name')->get();
            $customers = User::query()->where('role', 'member')->orderBy('first_name')->orderBy('last_name')->get();

            return view(self::ADMIN_.'merchandise.checkout', compact('products', 'customers'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Sales history report for desk/accounting handoff.
     *
     * @return View|RedirectResponse
     */
    public function history(Request $request): View|RedirectResponse
    {
        $timezone = 'Africa/Addis_Ababa';
        $validator = Validator::make($request->query(), [
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.merchandise.history')
                ->with(self::ERROR_, __('Choose a valid report date.'));
        }

        try {
            $selectedDate = $request->query('date')
                ? Carbon::createFromFormat('Y-m-d', (string) $request->query('date'), $timezone)->startOfDay()
                : Carbon::now($timezone)->startOfDay();

            $rangeStart = $selectedDate->copy()->subDay()->utc();
            $rangeEnd = $selectedDate->copy()->endOfDay()->addDay()->utc();

            $invoices = Invoice::query()
                ->where('invoice_source', 'merchandise')
                ->whereBetween('issued_date', [$rangeStart, $rangeEnd])
                ->with([
                    'customer',
                    'merchandiseSaleLines.product',
                    'payments' => fn ($query) => $query->orderBy('payment_date')->orderBy('id'),
                ])
                ->orderBy('issued_date')
                ->orderBy('id')
                ->get()
                ->filter(fn (Invoice $invoice) => Carbon::parse($invoice->issued_date)->setTimezone($timezone)->toDateString() === $selectedDate->toDateString())
                ->values();

            $groupedSales = $this->buildSalesHistoryGroups($invoices, $timezone);

            return view(self::ADMIN_.'merchandise.history', [
                'selectedDate' => $selectedDate->toDateString(),
                'groupedSales' => $groupedSales,
                'reportSummary' => [
                    'invoice_count' => (int) $invoices->count(),
                    'line_count' => (int) $invoices->sum(fn (Invoice $invoice) => $invoice->merchandiseSaleLines->count()),
                    'item_quantity' => (int) $invoices->sum(fn (Invoice $invoice) => $invoice->merchandiseSaleLines->sum('quantity')),
                    'gross_total' => (float) $invoices->sum('amount'),
                ],
            ]);
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'member')),
            ],
            'payment_method' => 'required|in:cash,bank',
            'payment_bank' => [
                Rule::requiredIf(fn () => $request->input('payment_method') === 'bank'),
                'nullable',
                'in:telebirr,cbe,boa',
            ],
            'bank_transaction_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Please check the checkout form and try again.'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $lines = [];
        foreach ($request->input('lines', []) as $line) {
            if (! empty($line['product_id']) && ! empty($line['quantity'])) {
                $lines[] = [
                    'product_id' => (int) $line['product_id'],
                    'quantity' => (int) $line['quantity'],
                ];
            }
        }

        if (count($lines) < 1) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Add at least one product line.'),
                ], 422);
            }

            return redirect()->back()->withInput()->with(self::ERROR_, __('Add at least one product line.'));
        }

        $rawUserId = $request->input('user_id');
        $payload = [
            'user_id' => ($rawUserId === null || $rawUserId === '') ? null : (int) $rawUserId,
            'lines' => $lines,
            'payment_method' => $request->input('payment_method'),
            'payment_bank' => $request->input('payment_bank'),
            'bank_transaction_number' => $request->input('bank_transaction_number'),
            'notes' => $request->input('notes'),
        ];

        if (($payload['payment_method'] ?? '') === 'cash') {
            $payload['payment_bank'] = null;
        }

        try {
            $invoice = $this->merchandiseRepository->checkout($payload);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Sale recorded.'),
                    'invoice_number' => $invoice->invoice_number,
                    'total' => (float) $invoice->amount,
                    'products' => $invoice->merchandiseSaleLines
                        ->map(fn ($line) => [
                            'id' => (int) $line->product_id,
                            'name' => (string) ($line->product?->name ?? ''),
                            'stock_quantity' => (int) ($line->product?->stock_quantity ?? 0),
                        ])
                        ->unique('id')
                        ->values(),
                ]);
            }

            return redirect()
                ->route('admin.merchandise.checkout')
                ->with(self::SUCCESS_, __('Sale recorded.'));
        } catch (Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * @return Collection<int, array{
     *     date:string,
     *     date_label:string,
     *     invoices:Collection<int, array{
     *         id:int,
     *         invoice_number:string,
     *         row_count:int,
     *         issued_at:string,
     *         customer_name:string,
     *         customer_phone:string|null,
     *         payment_label:string,
     *         payment_detail:string|null,
     *         quantity_total:int,
     *         invoice_total:float,
     *         status:string,
     *         lines:Collection<int, array{
     *             product_name:string,
     *             sku:string|null,
     *             quantity:int,
     *             unit_price:float,
     *             line_total:float
     *         }>
     *     }>,
     *     summary:array{invoice_count:int,line_count:int,item_quantity:int,gross_total:float}
     * }>
     */
    private function buildSalesHistoryGroups(Collection $invoices, string $timezone): Collection
    {
        return $invoices
            ->groupBy(fn (Invoice $invoice) => Carbon::parse($invoice->issued_date)->setTimezone($timezone)->toDateString())
            ->map(function (Collection $dayInvoices, string $dateKey) use ($timezone) {
                $formattedDate = Carbon::createFromFormat('Y-m-d', $dateKey, $timezone)->format('l, d M Y');

                $mappedInvoices = $dayInvoices->map(function (Invoice $invoice) use ($timezone) {
                    $lines = $invoice->merchandiseSaleLines
                        ->map(fn ($line) => [
                            'product_name' => (string) ($line->product?->name ?? __('Deleted product')),
                            'sku' => $line->product?->sku,
                            'quantity' => (int) $line->quantity,
                            'unit_price' => (float) $line->unit_price,
                            'line_total' => (float) $line->line_total,
                        ])
                        ->values();

                    $payment = $invoice->payments
                        ->firstWhere('status', 'completed') ?? $invoice->payments->first();

                    $paymentLabel = $payment ? ucfirst((string) $payment->payment_method) : __('N/A');
                    if ($payment?->payment_method === 'bank' && $payment->payment_bank) {
                        $paymentLabel .= ' / '.strtoupper((string) $payment->payment_bank);
                    }

                    $paymentDetail = $payment?->bank_transaction_number
                        ? __('Txn: :num', ['num' => $payment->bank_transaction_number])
                        : null;

                    return [
                        'id' => (int) $invoice->id,
                        'invoice_number' => (string) $invoice->invoice_number,
                        'row_count' => max(1, $lines->count()),
                        'issued_at' => Carbon::parse($invoice->issued_date)->setTimezone($timezone)->format('H:i'),
                        'customer_name' => $invoice->customer?->getName() ?? __('Walk-in'),
                        'customer_phone' => $invoice->customer?->phone,
                        'payment_label' => $paymentLabel,
                        'payment_detail' => $paymentDetail,
                        'quantity_total' => (int) $lines->sum('quantity'),
                        'invoice_total' => (float) $invoice->amount,
                        'status' => (string) $invoice->status,
                        'lines' => $lines,
                    ];
                })->values();

                return [
                    'date' => $dateKey,
                    'date_label' => $formattedDate,
                    'invoices' => $mappedInvoices,
                    'summary' => [
                        'invoice_count' => (int) $mappedInvoices->count(),
                        'line_count' => (int) $mappedInvoices->sum(fn (array $invoice) => $invoice['lines']->count()),
                        'item_quantity' => (int) $mappedInvoices->sum('quantity_total'),
                        'gross_total' => (float) $mappedInvoices->sum('invoice_total'),
                    ],
                ];
            })
            ->values();
    }
}
