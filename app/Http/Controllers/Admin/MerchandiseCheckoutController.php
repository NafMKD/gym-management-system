<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Repositories\MerchandiseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
