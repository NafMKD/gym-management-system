<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\NoUpdateNeededException;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {
    }

    /**
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'products.list');
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * @return View|RedirectResponse
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
     * @return RedirectResponse
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
     * @return View|RedirectResponse
     */
    public function show(Product $product): View|RedirectResponse
    {
        try {
            $product->loadCount('stockMovements');

            return view(self::ADMIN_.'products.view', compact('product'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * @return View|RedirectResponse
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
     * @return RedirectResponse
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
     *
     * @return RedirectResponse
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
     * @return RedirectResponse
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
     * @return JsonResponse
     */
    public function getListData(): JsonResponse
    {
        $query = Product::query();

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('name', fn ($row) => e($row->name))
            ->editColumn('sku', fn ($row) => $row->sku ? e($row->sku) : '—')
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
                return '
                    <a href="'.route('admin.products.view', $row->id).'" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> '.__('View').'
                    </a>
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
}
