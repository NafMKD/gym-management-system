<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\InteractsWithReportExports;
use App\Http\Controllers\Controller;
use App\Models\MerchandiseSaleLine;
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
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class MerchandiseCheckoutController extends Controller
{
    use InteractsWithReportExports;

    public function __construct(
        protected MerchandiseRepository $merchandiseRepository
    ) {
    }

    /**
     * POS checkout screen.
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
     */
    public function history(Request $request): View|RedirectResponse
    {
        return $this->renderHistoryReport($request, false);
    }

    /**
     * Print-friendly sales history report.
     */
    public function printHistory(Request $request): View|RedirectResponse
    {
        return $this->renderHistoryReport($request, true);
    }

    /**
     * Export sales history report as CSV.
     */
    public function exportHistoryCsv(Request $request): StreamedResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), array_merge($this->historyFilterRules(), [
            'columns' => 'nullable|array',
            'columns.*' => 'string|in:sale_date,invoice_number,customer_name,salesman,payment_method,payment_bank,product_name,sku,quantity,unit_price,line_total,invoice_total,status',
        ]));

        if ($validator->fails()) {
            return redirect()->route('admin.merchandise.history')->withErrors($validator)->withInput();
        }

        $filters = $this->normalizeHistoryFilters($validator->validated());
        $columns = $this->normalizeSelectedColumns(
            $filters['columns'] ?? null,
            $this->historyExportColumns(),
            $this->defaultHistoryColumns()
        );

        try {
            $filename = $this->buildReportFilename('merchandise-sales-report', $filters, 'start_date', 'end_date');
            $query = $this->merchandiseRepository
                ->getFilteredSalesLinesQuery($filters)
                ->orderBy('invoice_id')
                ->orderBy('id');

            return response()->streamDownload(function () use ($query, $filters, $columns) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, [__('Report'), __('Merchandise Sales Report')]);
                fputcsv($out, [__('Generated at'), now('Africa/Addis_Ababa')->format('Y-m-d H:i:s')]);
                fputcsv($out, [__('Filters'), $this->historyFilterSummary($filters)]);
                fputcsv($out, []);
                fputcsv($out, array_map(
                    fn ($column) => $this->historyExportColumns()[$column]['label'],
                    $columns
                ));

                $query->chunk(200, function ($chunk) use ($out, $columns) {
                    foreach ($chunk as $line) {
                        fputcsv($out, $this->buildHistoryExportRow($line, $columns));
                    }
                });
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        } catch (Throwable $e) {
            return redirect()->route('admin.merchandise.history')->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Record a merchandise sale.
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
     *         salesman_name:string,
     *         quantity_total:int,
     *         reported_total:float,
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
    private function buildSalesHistoryGroups(Collection $lines, string $timezone): Collection
    {
        return $lines
            ->sortBy(function (MerchandiseSaleLine $line) {
                $timestamp = $line->invoice?->issued_date
                    ? Carbon::parse($line->invoice->issued_date)->timestamp
                    : 0;

                return sprintf('%012d-%012d-%012d', $timestamp, (int) $line->invoice_id, (int) $line->id);
            })
            ->groupBy(fn (MerchandiseSaleLine $line) => Carbon::parse($line->invoice?->issued_date)->setTimezone($timezone)->toDateString())
            ->map(function (Collection $dayLines, string $dateKey) use ($timezone) {
                $formattedDate = Carbon::createFromFormat('Y-m-d', $dateKey, $timezone)->format('l, d M Y');

                $mappedInvoices = $dayLines
                    ->groupBy('invoice_id')
                    ->map(function (Collection $invoiceLines) use ($timezone) {
                        /** @var MerchandiseSaleLine $firstLine */
                        $firstLine = $invoiceLines->first();
                        $invoice = $firstLine->invoice;
                        $payment = $invoice?->payments->firstWhere('status', 'completed') ?? $invoice?->payments->first();

                        $paymentLabel = $payment ? ucfirst((string) $payment->payment_method) : __('N/A');
                        if ($payment?->payment_method === 'bank' && $payment->payment_bank) {
                            $paymentLabel .= ' / '.strtoupper((string) $payment->payment_bank);
                        }

                        $paymentDetail = $payment?->bank_transaction_number
                            ? __('Txn: :num', ['num' => $payment->bank_transaction_number])
                            : null;

                        $mappedLines = $invoiceLines
                            ->map(fn (MerchandiseSaleLine $line) => [
                                'product_name' => (string) ($line->product?->name ?? __('Deleted product')),
                                'sku' => $line->product?->sku,
                                'quantity' => (int) $line->quantity,
                                'unit_price' => (float) $line->unit_price,
                                'line_total' => (float) $line->line_total,
                            ])
                            ->values();

                        return [
                            'id' => (int) ($invoice?->id ?? 0),
                            'invoice_number' => (string) ($invoice?->invoice_number ?? ''),
                            'row_count' => max(1, $mappedLines->count()),
                            'issued_at' => Carbon::parse($invoice?->issued_date)->setTimezone($timezone)->format('H:i'),
                            'customer_name' => $invoice?->customer?->getName() ?? __('Walk-in'),
                            'customer_phone' => $invoice?->customer?->phone,
                            'payment_label' => $paymentLabel,
                            'payment_detail' => $paymentDetail,
                            'salesman_name' => $invoice?->createdBy?->getName() ?? __('Legacy / Unknown'),
                            'quantity_total' => (int) $mappedLines->sum('quantity'),
                            'reported_total' => (float) $mappedLines->sum('line_total'),
                            'invoice_total' => (float) ($invoice?->amount ?? 0),
                            'status' => (string) ($invoice?->status ?? ''),
                            'lines' => $mappedLines,
                        ];
                    })
                    ->values();

                return [
                    'date' => $dateKey,
                    'date_label' => $formattedDate,
                    'invoices' => $mappedInvoices,
                    'summary' => [
                        'invoice_count' => (int) $mappedInvoices->count(),
                        'line_count' => (int) $mappedInvoices->sum(fn (array $invoice) => $invoice['lines']->count()),
                        'item_quantity' => (int) $mappedInvoices->sum('quantity_total'),
                        'gross_total' => (float) $mappedInvoices->sum('reported_total'),
                    ],
                ];
            })
            ->values();
    }

    /**
     * @return array<string, string>
     */
    private function historyFilterRules(): array
    {
        return [
            'date' => 'nullable|date_format:Y-m-d',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'product_id' => 'nullable|integer|exists:products,id',
            'salesman_id' => 'nullable|integer|exists:users,id',
            'invoice_number' => 'nullable|string|max:50',
            'payment_method' => 'nullable|in:cash,bank',
            'payment_bank' => 'nullable|in:telebirr,cbe,boa',
            'customer_id' => 'nullable|integer|exists:users,id',
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function normalizeHistoryFilters(array $filters): array
    {
        if (! empty($filters['date']) && empty($filters['start_date']) && empty($filters['end_date'])) {
            $filters['start_date'] = $filters['date'];
            $filters['end_date'] = $filters['date'];
        }

        if (empty($filters['date']) && empty($filters['start_date']) && empty($filters['end_date'])) {
            $today = now('Africa/Addis_Ababa')->toDateString();
            $filters['date'] = $today;
            $filters['start_date'] = $today;
            $filters['end_date'] = $today;
        }

        if (empty($filters['date']) && ! empty($filters['start_date']) && ($filters['start_date'] === ($filters['end_date'] ?? null))) {
            $filters['date'] = $filters['start_date'];
        }

        return $filters;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    private function getHistoryProducts()
    {
        return Product::query()->orderBy('name')->get(['id', 'name', 'sku']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function getSalesActors()
    {
        return User::query()
            ->whereIn('role', ['admin', 'reception', 'accountant'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function getHistoryCustomers()
    {
        return User::query()
            ->where('role', 'member')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'phone']);
    }

    /**
     * @return array<string, array{label:string,value:callable}>
     */
    private function historyExportColumns(): array
    {
        return [
            'sale_date' => [
                'label' => 'Sale Date',
                'value' => fn (MerchandiseSaleLine $line) => $this->formatDateTime($line->invoice?->issued_date, 'Y-m-d H:i:s'),
            ],
            'invoice_number' => [
                'label' => 'Invoice Number',
                'value' => fn (MerchandiseSaleLine $line) => $line->invoice?->invoice_number ?? '',
            ],
            'customer_name' => [
                'label' => 'Customer',
                'value' => fn (MerchandiseSaleLine $line) => $line->invoice?->customer?->getName() ?? __('Walk-in'),
            ],
            'salesman' => [
                'label' => 'Salesman',
                'value' => fn (MerchandiseSaleLine $line) => $line->invoice?->createdBy?->getName() ?? __('Legacy / Unknown'),
            ],
            'payment_method' => [
                'label' => 'Payment Method',
                'value' => fn (MerchandiseSaleLine $line) => ucfirst((string) ($line->invoice?->payments->firstWhere('status', 'completed')?->payment_method ?? '')),
            ],
            'payment_bank' => [
                'label' => 'Payment Bank',
                'value' => function (MerchandiseSaleLine $line) {
                    $payment = $line->invoice?->payments->firstWhere('status', 'completed') ?? $line->invoice?->payments->first();

                    return $payment?->payment_bank ? strtoupper((string) $payment->payment_bank) : '-';
                },
            ],
            'product_name' => [
                'label' => 'Product',
                'value' => fn (MerchandiseSaleLine $line) => $line->product?->name ?? __('Deleted product'),
            ],
            'sku' => [
                'label' => 'SKU',
                'value' => fn (MerchandiseSaleLine $line) => $line->product?->sku ?? '-',
            ],
            'quantity' => [
                'label' => 'Quantity',
                'value' => fn (MerchandiseSaleLine $line) => $line->quantity,
            ],
            'unit_price' => [
                'label' => 'Unit Price',
                'value' => fn (MerchandiseSaleLine $line) => number_format((float) $line->unit_price, 2, '.', ''),
            ],
            'line_total' => [
                'label' => 'Line Total',
                'value' => fn (MerchandiseSaleLine $line) => number_format((float) $line->line_total, 2, '.', ''),
            ],
            'invoice_total' => [
                'label' => 'Invoice Total',
                'value' => fn (MerchandiseSaleLine $line) => number_format((float) ($line->invoice?->amount ?? 0), 2, '.', ''),
            ],
            'status' => [
                'label' => 'Invoice Status',
                'value' => fn (MerchandiseSaleLine $line) => ucfirst((string) ($line->invoice?->status ?? '')),
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function defaultHistoryColumns(): array
    {
        return ['sale_date', 'invoice_number', 'customer_name', 'salesman', 'payment_method', 'product_name', 'sku', 'quantity', 'unit_price', 'line_total', 'invoice_total', 'status'];
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    private function buildHistoryExportRow(MerchandiseSaleLine $line, array $columns): array
    {
        return array_map(function ($column) use ($line) {
            return (string) call_user_func($this->historyExportColumns()[$column]['value'], $line);
        }, $columns);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function historyFilterSummary(array $filters): string
    {
        return implode(' | ', [
            __('From').': '.($filters['start_date'] ?? __('Any')),
            __('To').': '.($filters['end_date'] ?? __('Any')),
            __('Product').': '.($this->productName($filters['product_id'] ?? null) ?? __('All')),
            __('Salesman').': '.($this->salesmanName($filters['salesman_id'] ?? null) ?? __('All')),
            __('Invoice').': '.($filters['invoice_number'] ?? __('All')),
            __('Payment method').': '.(($filters['payment_method'] ?? null) ? ucfirst((string) $filters['payment_method']) : __('All')),
            __('Payment bank').': '.(($filters['payment_bank'] ?? null) ? strtoupper((string) $filters['payment_bank']) : __('All')),
            __('Customer').': '.($this->customerName($filters['customer_id'] ?? null) ?? __('All')),
        ]);
    }

    private function productName(mixed $productId): ?string
    {
        if (empty($productId)) {
            return null;
        }

        return Product::query()->whereKey((int) $productId)->value('name');
    }

    private function salesmanName(mixed $salesmanId): ?string
    {
        if (empty($salesmanId)) {
            return null;
        }

        $salesman = User::query()->find((int) $salesmanId);

        return $salesman?->getName();
    }

    private function customerName(mixed $customerId): ?string
    {
        if (empty($customerId)) {
            return null;
        }

        $customer = User::query()->find((int) $customerId);

        return $customer?->getName();
    }

    private function formatDateTime(mixed $value, string $format = 'd/m/Y H:i'): string
    {
        if (blank($value)) {
            return '-';
        }

        return Carbon::parse($value)->setTimezone('Africa/Addis_Ababa')->format($format);
    }

    private function renderHistoryReport(Request $request, bool $autoPrint): View|RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->historyFilterRules());

        if ($validator->fails()) {
            return redirect()
                ->route('admin.merchandise.history')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $timezone = 'Africa/Addis_Ababa';
            $filters = $this->normalizeHistoryFilters($validator->validated());
            $lines = $this->merchandiseRepository
                ->getFilteredSalesLinesQuery($filters)
                ->get();

            $groupedSales = $this->buildSalesHistoryGroups($lines, $timezone);
            $reportSummary = $this->merchandiseRepository->getSalesSummary($filters);

            return view(self::ADMIN_.'merchandise.history', [
                'autoPrint' => $autoPrint,
                'filters' => $filters,
                'groupedSales' => $groupedSales,
                'products' => $this->getHistoryProducts(),
                'salesmen' => $this->getSalesActors(),
                'customers' => $this->getHistoryCustomers(),
                'reportSummary' => $reportSummary,
                'reportGeneratedAt' => now('Africa/Addis_Ababa'),
                'filterSummary' => $this->historyFilterSummary($filters),
                'exportColumns' => $this->historyExportColumns(),
                'defaultExportColumns' => $this->defaultHistoryColumns(),
            ]);
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }
}
