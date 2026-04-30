<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\InteractsWithReportExports;
use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\User;
use App\Repositories\InvoiceRepository;
use App\Support\DataTables\UserNameSearch;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    use InteractsWithReportExports;

    public function __construct(
        protected InvoiceRepository $invoiceRepository
    ) {
    }

    /**
     * Display a listing of the invoices.
     */
    public function index(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'invoices.list', [
                'creators' => $this->getInvoiceCreators(),
                'exportColumns' => $this->invoiceExportColumns(),
                'defaultExportColumns' => $this->defaultInvoiceColumns(),
            ]);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Display a specific invoice.
     */
    public function show(Invoice $invoice): View|RedirectResponse
    {
        try {
            $invoice->load([
                'membership.user',
                'membership.package',
                'customer',
                'createdBy',
                'merchandiseSaleLines.product',
                'payments.createdBy',
            ]);

            return view(self::ADMIN_.'invoices.view', compact('invoice'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Email the invoice summary to the member.
     */
    public function sendEmail(Invoice $invoice): RedirectResponse
    {
        try {
            $invoice->loadMissing(['membership.user', 'customer', 'merchandiseSaleLines.product']);
            if (($invoice->invoice_source ?? 'membership') === 'merchandise') {
                $email = $invoice->customer?->email;
            } else {
                $email = $invoice->membership?->user?->email;
            }
            if (! $email) {
                return redirect()->back()->with(self::ERROR_, __('No email address on file for this invoice.'));
            }

            Mail::to($email)->send(new InvoiceMail($invoice));

            return redirect()->back()->with(self::SUCCESS_, __('Invoice email sent.'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Download invoice as PDF (DomPDF).
     *
     * @return \Symfony\Component\HttpFoundation\Response|RedirectResponse
     */
    public function downloadPdf(Invoice $invoice)
    {
        try {
            $invoice->load([
                'membership.user',
                'membership.package',
                'customer',
                'createdBy',
                'merchandiseSaleLines.product',
            ]);

            return Pdf::loadView('pages.admin.invoices.pdf', ['invoice' => $invoice])
                ->setPaper('a4')
                ->download('invoice-'.$invoice->invoice_number.'.pdf');
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Retrieve invoice data for the report listing.
     */
    public function getInvoicesData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->invoiceFilterRules());

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Invalid input values.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $filters = $validator->validated();
        $query = $this->invoiceRepository->getFilteredInvoicesQuery($filters);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('invoice_number', function (Invoice $invoice) {
                return '<span class="font-weight-bold">'.e($invoice->invoice_number).'</span>';
            })
            ->addColumn('name', function (Invoice $invoice) {
                if (($invoice->invoice_source ?? 'membership') === 'merchandise') {
                    return $invoice->customer?->getName() ?? __('Walk-in');
                }

                return $invoice->membership?->user?->getName() ?? 'N/A';
            })
            ->addColumn('source', function (Invoice $invoice) {
                return ucfirst((string) ($invoice->invoice_source ?? 'membership'));
            })
            ->addColumn('package', function (Invoice $invoice) {
                if (($invoice->invoice_source ?? 'membership') === 'merchandise') {
                    return __('Merchandise');
                }

                return is_null($invoice->membership?->package?->name)
                    ? __('Custom')
                    : ucwords((string) $invoice->membership->package?->name);
            })
            ->editColumn('amount', fn (Invoice $invoice) => number_format((float) $invoice->amount, 2))
            ->editColumn('issued_date', fn (Invoice $invoice) => $this->formatDateTime($invoice->issued_date))
            ->addColumn('created_by', function (Invoice $invoice) {
                return $invoice->createdBy?->getName() ?? __('Legacy / Unknown');
            })
            ->editColumn('status', function (Invoice $invoice) {
                $badgeClass = match ($invoice->status) {
                    'paid' => 'badge-success',
                    'unpaid' => 'badge-warning',
                    default => 'badge-secondary',
                };

                return '<span class="badge '.$badgeClass.'">'.ucwords((string) $invoice->status).'</span>';
            })
            ->addColumn('action', function (Invoice $invoice) {
                return '
                    <a href="' . route('admin.invoices.view', $invoice->id) . '" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> '.__('View').'
                    </a>
                    <a href="' . route('admin.invoices.pdf', $invoice->id) . '" class="btn btn-secondary btn-xs btn-flat">
                        <i class="fas fa-file-pdf"></i> '.__('PDF').'
                    </a>
                ';
            })
            ->filterColumn('invoice_number', function ($query, $keyword) {
                $query->where('invoice_number', 'like', '%'.UserNameSearch::escapeLike($keyword).'%');
            })
            ->filterColumn('name', function ($query, $keyword) {
                UserNameSearch::applyForInvoicePersonName($query, $keyword);
            })
            ->filterColumn('package', function ($query, $keyword) {
                UserNameSearch::applyForInvoicePackageDisplay($query, $keyword);
            })
            ->filterColumn('source', function ($query, $keyword) {
                $query->where('invoice_source', 'like', '%'.UserNameSearch::escapeLike($keyword).'%');
            })
            ->filterColumn('created_by', function ($query, $keyword) {
                $query->whereHas('createdBy', function ($creatorQuery) use ($keyword) {
                    UserNameSearch::applyToUserQuery($creatorQuery, $keyword);
                });
            })
            ->rawColumns(['invoice_number', 'status', 'action'])
            ->make(true);
    }

    /**
     * Export filtered invoices as CSV.
     */
    public function exportInvoicesCsv(Request $request): StreamedResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), array_merge($this->invoiceFilterRules(), [
            'columns' => 'nullable|array',
            'columns.*' => 'string|in:invoice_number,name,source,package,amount,status,issued_date,due_date,created_by',
        ]));

        if ($validator->fails()) {
            return redirect()->route('admin.invoices.list')->withErrors($validator)->withInput();
        }

        $filters = $validator->validated();
        $columns = $this->normalizeSelectedColumns(
            $filters['columns'] ?? null,
            $this->invoiceExportColumns(),
            $this->defaultInvoiceColumns()
        );

        try {
            $filename = $this->buildReportFilename('invoices-report', $filters, 'issued_from', 'issued_to');
            $query = $this->invoiceRepository
                ->getFilteredInvoicesQuery($filters)
                ->orderByDesc('issued_date')
                ->orderByDesc('id');

            return response()->streamDownload(function () use ($query, $filters, $columns) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, [__('Report'), __('Invoices Report')]);
                fputcsv($out, [__('Generated at'), now('Africa/Addis_Ababa')->format('Y-m-d H:i:s')]);
                fputcsv($out, [__('Filters'), $this->invoiceFilterSummary($filters)]);
                fputcsv($out, []);
                fputcsv($out, array_map(
                    fn ($column) => $this->invoiceExportColumns()[$column]['label'],
                    $columns
                ));

                $query->chunk(200, function ($chunk) use ($out, $columns) {
                    foreach ($chunk as $invoice) {
                        fputcsv($out, $this->buildInvoiceExportRow($invoice, $columns));
                    }
                });
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        } catch (Throwable $e) {
            return redirect()->route('admin.invoices.list')->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Print-friendly invoice report view.
     */
    public function printInvoicesReport(Request $request): View|RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->invoiceFilterRules());

        if ($validator->fails()) {
            return redirect()->route('admin.invoices.list')->withErrors($validator)->withInput();
        }

        $filters = $validator->validated();

        try {
            $invoices = $this->invoiceRepository
                ->getFilteredInvoicesQuery($filters)
                ->orderByDesc('issued_date')
                ->orderByDesc('id')
                ->get();

            return view(self::ADMIN_.'invoices.print', [
                'invoices' => $invoices,
                'filters' => $filters,
                'reportGeneratedAt' => now('Africa/Addis_Ababa'),
                'filterSummary' => $this->invoiceFilterSummary($filters),
            ]);
        } catch (Throwable $e) {
            return redirect()->route('admin.invoices.list')->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * @return array<string, string>
     */
    private function invoiceFilterRules(): array
    {
        return [
            'issued_from' => 'nullable|date',
            'issued_to' => 'nullable|date|after_or_equal:issued_from',
            'status' => 'nullable|in:paid,unpaid',
            'invoice_source' => 'nullable|in:membership,merchandise',
            'created_by_user_id' => 'nullable|integer|exists:users,id',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function getInvoiceCreators()
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
    private function invoiceExportColumns(): array
    {
        return [
            'invoice_number' => [
                'label' => 'Invoice Number',
                'value' => fn (Invoice $invoice) => $invoice->invoice_number,
            ],
            'name' => [
                'label' => 'Customer / Member',
                'value' => fn (Invoice $invoice) => ($invoice->invoice_source ?? 'membership') === 'merchandise'
                    ? ($invoice->customer?->getName() ?? __('Walk-in'))
                    : ($invoice->membership?->user?->getName() ?? 'N/A'),
            ],
            'source' => [
                'label' => 'Source',
                'value' => fn (Invoice $invoice) => ucfirst((string) ($invoice->invoice_source ?? 'membership')),
            ],
            'package' => [
                'label' => 'Package / Context',
                'value' => fn (Invoice $invoice) => ($invoice->invoice_source ?? 'membership') === 'merchandise'
                    ? __('Merchandise')
                    : ($invoice->membership?->package?->name ?? __('Custom')),
            ],
            'amount' => [
                'label' => 'Amount',
                'value' => fn (Invoice $invoice) => number_format((float) $invoice->amount, 2, '.', ''),
            ],
            'status' => [
                'label' => 'Status',
                'value' => fn (Invoice $invoice) => ucfirst((string) $invoice->status),
            ],
            'issued_date' => [
                'label' => 'Issue Date',
                'value' => fn (Invoice $invoice) => $this->formatDateTime($invoice->issued_date, 'Y-m-d H:i:s'),
            ],
            'due_date' => [
                'label' => 'Due Date',
                'value' => fn (Invoice $invoice) => $this->formatDateTime($invoice->due_date, 'Y-m-d H:i:s'),
            ],
            'created_by' => [
                'label' => 'Created By',
                'value' => fn (Invoice $invoice) => $invoice->createdBy?->getName() ?? __('Legacy / Unknown'),
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function defaultInvoiceColumns(): array
    {
        return ['invoice_number', 'name', 'source', 'package', 'amount', 'status', 'issued_date', 'created_by'];
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    private function buildInvoiceExportRow(Invoice $invoice, array $columns): array
    {
        return array_map(function ($column) use ($invoice) {
            return (string) call_user_func($this->invoiceExportColumns()[$column]['value'], $invoice);
        }, $columns);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function invoiceFilterSummary(array $filters): string
    {
        $summary = [
            __('Issue from').': '.($filters['issued_from'] ?? __('Any')),
            __('Issue to').': '.($filters['issued_to'] ?? __('Any')),
            __('Status').': '.(($filters['status'] ?? null) ? ucfirst((string) $filters['status']) : __('All')),
            __('Source').': '.(($filters['invoice_source'] ?? null) ? ucfirst((string) $filters['invoice_source']) : __('All')),
            __('Created by').': '.($this->creatorName($filters['created_by_user_id'] ?? null) ?? __('All')),
        ];

        return implode(' | ', $summary);
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
}
