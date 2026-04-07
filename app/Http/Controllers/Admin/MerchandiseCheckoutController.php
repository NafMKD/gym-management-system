<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Repositories\MerchandiseRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
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

            if (Auth::user()->role === 'reception') {
                return redirect()
                    ->route('reception.home')
                    ->with(self::SUCCESS_, __('Sale recorded. Invoice #:num', ['num' => $invoice->invoice_number]));
            }

            return redirect()
                ->route('admin.invoices.view', $invoice)
                ->with(self::SUCCESS_, __('Sale recorded.'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, $e->getMessage());
        }
    }
}
