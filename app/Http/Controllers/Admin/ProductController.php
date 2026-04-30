<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\NoUpdateNeededException;
use App\Http\Controllers\Concerns\InteractsWithReportExports;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Repositories\MerchandiseRepository;
use App\Repositories\ProductRepository;
use App\Support\DataTables\UserNameSearch;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    use InteractsWithReportExports;

    public function __construct(
        protected ProductRepository $productRepository,
        protected MerchandiseRepository $merchandiseRepository
    ) {
    }

    /**
     * Product inventory + movement report page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->movementFilterRules());

        if ($validator->fails()) {
            return redirect()->route('admin.products.list')->withErrors($validator)->withInput();
        }

        try {
            $filters = $validator->validated();
            $summary = $this->productRepository->getMovementSummary($filters);
            $salesSummary = $this->merchandiseRepository->getSalesSummary([
                'start_date' => $filters['start_date'] ?? null,
                'end_date' => $filters['end_date'] ?? null,
                'product_id' => $filters['product_id'] ?? null,
                'salesman_id' => $filters['actor_id'] ?? null,
            ]);

            return view(self::ADMIN_.'products.list', [
                'filters' => $filters,
                'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku']),
                'actors' => $this->getProductActors(),
                'summary' => array_merge($summary, [
                    'sales_revenue' => (float) $salesSummary['gross_total'],
                ]),
                'exportColumns' => $this->movementExportColumns(),
                'defaultExportColumns' => $this->defaultMovementColumns(),
            ]);
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Product add page.
     */
    public function create(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'products.add');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Store a new product.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'description' => 'nullable|string|max:2000',
            'unit_price' => 'required|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only([
            'name', 'sku', 'description', 'unit_price', 'stock_quantity', 'low_stock_threshold', 'is_active',
        ]);

        try {
            $this->productRepository->store($attributes);

            return redirect()->route('admin.products.list')->with(self::SUCCESS_, __('Product').self::SUCCESS_STORE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Product detail page.
     */
    public function show(Product $product): View|RedirectResponse
    {
        try {
            $product->loadCount('stockMovements');
            $recentMovements = $product->stockMovements()
                ->with(['user', 'invoice.customer'])
                ->latest('created_at')
                ->latest('id')
                ->limit(25)
                ->get();

            return view(self::ADMIN_.'products.view', compact('product', 'recentMovements'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Product edit page.
     */
    public function edit(Product $product): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'products.edit', compact('product'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Update product.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,'.$product->id,
            'description' => 'nullable|string|max:2000',
            'unit_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only([
            'name', 'sku', 'description', 'unit_price', 'low_stock_threshold', 'is_active',
        ]);

        try {
            $this->productRepository->update($product, $attributes);

            return redirect()->route('admin.products.view', $product)->with(self::SUCCESS_, self::SUCCESS_UPDATE);
        } catch (NoUpdateNeededException $e) {
            return redirect()->back()->withInput()->with(self::SUCCESS_, self::SUCCESS_NO_UPDATE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Restock or adjust on-hand quantity (creates stock_movements row).
     */
    public function updateStock(Request $request, Product $product): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'stock_op' => 'required|in:restock,adjustment',
            'quantity' => 'required_if:stock_op,restock|nullable|integer|min:1',
            'delta' => 'required_if:stock_op,adjustment|nullable|integer|not_in:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            if ($request->input('stock_op') === 'restock') {
                $this->productRepository->applyStockChange(
                    $product,
                    (int) $request->input('quantity'),
                    'restock',
                    $request->input('notes')
                );
            } else {
                $this->productRepository->applyStockChange(
                    $product,
                    (int) $request->input('delta'),
                    'adjustment',
                    $request->input('notes')
                );
            }

            return redirect()->route('admin.products.view', $product)->with(self::SUCCESS_, __('Stock updated.'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            $this->productRepository->destroy($product);

            return redirect()->route('admin.products.list')->with(self::SUCCESS_, self::SUCCESS_DELETE);
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Product snapshot table data.
     */
    public function getListData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'nullable|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Invalid input values.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = $this->productRepository->getFilteredProductsQuery($validator->validated());

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('name', fn ($row) => e($row->name))
            ->editColumn('sku', fn ($row) => $row->sku ? e($row->sku) : '-')
            ->editColumn('unit_price', fn ($row) => number_format((float) $row->unit_price, 2))
            ->editColumn('stock_quantity', function ($row) {
                $q = (int) $row->stock_quantity;
                if ($row->isLowStock()) {
                    return '<span class="badge badge-warning">'.$q.' ('.__('Low').')</span>';
                }

                return (string) $q;
            })
            ->editColumn('is_active', function ($row) {
                return $row->is_active
                    ? '<span class="badge badge-success">'.__('Yes').'</span>'
                    : '<span class="badge badge-secondary">'.__('No').'</span>';
            })
            ->addColumn('action', function ($row) {
                $action = '
                    <a href="'.route('admin.products.view', $row->id).'" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> '.__('View').'
                    </a>
                ';

                if (auth()->user()?->role !== 'admin') {
                    return $action;
                }

                return $action . '
                    <a href="'.route('admin.products.edit', $row->id).'" class="btn btn-primary btn-xs btn-flat">
                        <i class="fas fa-edit"></i> '.__('Edit').'
                    </a>
                    <a href="'.route('admin.products.delete', $row->id).'"
                        onclick="if(confirm(\''.__('Are you sure?').'\') == false){event.preventDefault()}"
                        class="btn btn-danger btn-xs btn-flat">
                        <i class="fas fa-trash"></i> '.__('Delete').'
                    </a>
                ';
            })
            ->rawColumns(['action', 'stock_quantity', 'is_active'])
            ->make(true);
    }

    /**
     * Stock movement ledger data.
     */
    public function getMovementData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->movementFilterRules());

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Invalid input values.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = $this->productRepository
            ->getFilteredStockMovementsQuery($validator->validated())
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('created_at', fn (StockMovement $movement) => $this->formatDateTime($movement->created_at))
            ->addColumn('product_name', fn (StockMovement $movement) => $movement->product?->name ?? __('Deleted product'))
            ->addColumn('sku', fn (StockMovement $movement) => $movement->product?->sku ?? '-')
            ->editColumn('reason', fn (StockMovement $movement) => ucfirst((string) $movement->reason))
            ->editColumn('quantity_change', function (StockMovement $movement) {
                $class = $movement->quantity_change < 0 ? 'text-danger' : 'text-success';

                return '<span class="'.$class.' font-weight-bold">'.number_format($movement->quantity_change).'</span>';
            })
            ->addColumn('actor', fn (StockMovement $movement) => $movement->user?->getName() ?? __('Legacy / Unknown'))
            ->addColumn('invoice_number', fn (StockMovement $movement) => $movement->invoice?->invoice_number ?? '-')
            ->addColumn('customer_name', fn (StockMovement $movement) => $movement->invoice?->customer?->getName() ?? '-')
            ->editColumn('notes', fn (StockMovement $movement) => $movement->notes ?: '-')
            ->filterColumn('product_name', function ($query, $keyword) {
                $query->whereHas('product', function ($productQuery) use ($keyword) {
                    $productQuery->where('name', 'like', '%'.UserNameSearch::escapeLike($keyword).'%');
                });
            })
            ->filterColumn('sku', function ($query, $keyword) {
                $query->whereHas('product', function ($productQuery) use ($keyword) {
                    $productQuery->where('sku', 'like', '%'.UserNameSearch::escapeLike($keyword).'%');
                });
            })
            ->filterColumn('actor', function ($query, $keyword) {
                $query->whereHas('user', function ($userQuery) use ($keyword) {
                    UserNameSearch::applyToUserQuery($userQuery, $keyword);
                });
            })
            ->filterColumn('invoice_number', function ($query, $keyword) {
                $query->whereHas('invoice', function ($invoiceQuery) use ($keyword) {
                    $invoiceQuery->where('invoice_number', 'like', '%'.UserNameSearch::escapeLike($keyword).'%');
                });
            })
            ->rawColumns(['quantity_change'])
            ->make(true);
    }

    /**
     * Export the filtered stock movement report as CSV.
     */
    public function exportMovementsCsv(Request $request): StreamedResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), array_merge($this->movementFilterRules(), [
            'columns' => 'nullable|array',
            'columns.*' => 'string|in:movement_date,product_name,sku,reason,quantity_change,actor,invoice_number,customer_name,notes',
        ]));

        if ($validator->fails()) {
            return redirect()->route('admin.products.list')->withErrors($validator)->withInput();
        }

        $filters = $validator->validated();
        $columns = $this->normalizeSelectedColumns(
            $filters['columns'] ?? null,
            $this->movementExportColumns(),
            $this->defaultMovementColumns()
        );

        try {
            $filename = $this->buildReportFilename('product-movements-report', $filters, 'start_date', 'end_date');
            $query = $this->productRepository
                ->getFilteredStockMovementsQuery($filters)
                ->orderByDesc('created_at')
                ->orderByDesc('id');

            return response()->streamDownload(function () use ($query, $filters, $columns) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, [__('Report'), __('Product Movement Report')]);
                fputcsv($out, [__('Generated at'), now('Africa/Addis_Ababa')->format('Y-m-d H:i:s')]);
                fputcsv($out, [__('Filters'), $this->movementFilterSummary($filters)]);
                fputcsv($out, []);
                fputcsv($out, array_map(
                    fn ($column) => $this->movementExportColumns()[$column]['label'],
                    $columns
                ));

                $query->chunk(200, function ($chunk) use ($out, $columns) {
                    foreach ($chunk as $movement) {
                        fputcsv($out, $this->buildMovementExportRow($movement, $columns));
                    }
                });
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        } catch (Throwable $e) {
            return redirect()->route('admin.products.list')->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Print-friendly stock movement report.
     */
    public function printMovementsReport(Request $request): View|RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->movementFilterRules());

        if ($validator->fails()) {
            return redirect()->route('admin.products.list')->withErrors($validator)->withInput();
        }

        $filters = $validator->validated();

        try {
            $movements = $this->productRepository
                ->getFilteredStockMovementsQuery($filters)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();

            $summary = $this->productRepository->getMovementSummary($filters);
            $salesSummary = $this->merchandiseRepository->getSalesSummary([
                'start_date' => $filters['start_date'] ?? null,
                'end_date' => $filters['end_date'] ?? null,
                'product_id' => $filters['product_id'] ?? null,
                'salesman_id' => $filters['actor_id'] ?? null,
            ]);

            $inventory = $this->productRepository
                ->getFilteredProductsQuery($filters)
                ->orderBy('name')
                ->get();

            return view(self::ADMIN_.'products.print', [
                'movements' => $movements,
                'inventory' => $inventory,
                'filters' => $filters,
                'summary' => array_merge($summary, [
                    'sales_revenue' => (float) $salesSummary['gross_total'],
                ]),
                'filterSummary' => $this->movementFilterSummary($filters),
                'reportGeneratedAt' => now('Africa/Addis_Ababa'),
            ]);
        } catch (Throwable $e) {
            return redirect()->route('admin.products.list')->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * @return array<string, string>
     */
    private function movementFilterRules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'product_id' => 'nullable|integer|exists:products,id',
            'reason' => 'nullable|in:restock,sale,adjustment',
            'actor_id' => 'nullable|integer|exists:users,id',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function getProductActors()
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
    private function movementExportColumns(): array
    {
        return [
            'movement_date' => [
                'label' => 'Movement Date',
                'value' => fn (StockMovement $movement) => $this->formatDateTime($movement->created_at, 'Y-m-d H:i:s'),
            ],
            'product_name' => [
                'label' => 'Product',
                'value' => fn (StockMovement $movement) => $movement->product?->name ?? __('Deleted product'),
            ],
            'sku' => [
                'label' => 'SKU',
                'value' => fn (StockMovement $movement) => $movement->product?->sku ?? '-',
            ],
            'reason' => [
                'label' => 'Reason',
                'value' => fn (StockMovement $movement) => ucfirst((string) $movement->reason),
            ],
            'quantity_change' => [
                'label' => 'Quantity Change',
                'value' => fn (StockMovement $movement) => $movement->quantity_change,
            ],
            'actor' => [
                'label' => 'Actor / Salesman',
                'value' => fn (StockMovement $movement) => $movement->user?->getName() ?? __('Legacy / Unknown'),
            ],
            'invoice_number' => [
                'label' => 'Invoice Number',
                'value' => fn (StockMovement $movement) => $movement->invoice?->invoice_number ?? '-',
            ],
            'customer_name' => [
                'label' => 'Customer',
                'value' => fn (StockMovement $movement) => $movement->invoice?->customer?->getName() ?? '-',
            ],
            'notes' => [
                'label' => 'Notes',
                'value' => fn (StockMovement $movement) => $movement->notes ?: '-',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function defaultMovementColumns(): array
    {
        return ['movement_date', 'product_name', 'sku', 'reason', 'quantity_change', 'actor', 'invoice_number', 'customer_name', 'notes'];
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<int, string>
     */
    private function buildMovementExportRow(StockMovement $movement, array $columns): array
    {
        return array_map(function ($column) use ($movement) {
            return (string) call_user_func($this->movementExportColumns()[$column]['value'], $movement);
        }, $columns);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function movementFilterSummary(array $filters): string
    {
        return implode(' | ', [
            __('From').': '.($filters['start_date'] ?? __('Any')),
            __('To').': '.($filters['end_date'] ?? __('Any')),
            __('Product').': '.($this->productName($filters['product_id'] ?? null) ?? __('All')),
            __('Reason').': '.(($filters['reason'] ?? null) ? ucfirst((string) $filters['reason']) : __('All')),
            __('Actor').': '.($this->actorName($filters['actor_id'] ?? null) ?? __('All')),
        ]);
    }

    private function productName(mixed $productId): ?string
    {
        if (empty($productId)) {
            return null;
        }

        return Product::query()->whereKey((int) $productId)->value('name');
    }

    private function actorName(mixed $actorId): ?string
    {
        if (empty($actorId)) {
            return null;
        }

        $actor = User::query()->find((int) $actorId);

        return $actor?->getName();
    }

    private function formatDateTime(mixed $value, string $format = 'd/m/Y H:i'): string
    {
        if (blank($value)) {
            return '-';
        }

        return Carbon::parse($value)->setTimezone('Africa/Addis_Ababa')->format($format);
    }
}
