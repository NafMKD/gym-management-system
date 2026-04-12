<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DataTables\UserNameSearch;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{

    /**
     * Display a listing of the invoices.
     * 
     * @return View
     */
    public function index(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'invoices.list');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }


    /**
     * Display a specific invoice.
     * 
     * @param Invoice $invoice
     * @return View
     */
    public function show(Invoice $invoice): View|RedirectResponse
    {
        try {
            $invoice->load([
                'membership.user',
                'membership.package',
                'customer',
                'merchandiseSaleLines.product',
                'payments',
            ]);

            return view(self::ADMIN_.'invoices.view', compact('invoice'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Email the invoice summary to the member.
     *
     * @return RedirectResponse
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
                'merchandiseSaleLines.product',
            ]);

            return Pdf::loadView('pages.admin.invoices.pdf', ['invoice' => $invoice])
                ->download('invoice-'.$invoice->invoice_number.'.pdf');
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Retrieves user data from the database.
     *
     * @return  JsonResponse
     */
    public function getInvoicesData(): JsonResponse
    {
        $query = Invoice::query()->with(['membership.user', 'membership.package', 'customer']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('name', function ($row) {
                if (($row->invoice_source ?? 'membership') === 'merchandise') {
                    return $row->customer?->getName() ?? __('Walk-in');
                }

                return $row->membership?->user?->getName() ?? 'N/A';
            })
            ->editColumn('package', function ($row) {
                if (($row->invoice_source ?? 'membership') === 'merchandise') {
                    return __('Merchandise');
                }

                return is_null($row->membership?->package?->name) ? __('Custom') : ucwords((string) $row->membership->package?->name);
            })
            ->editColumn('amount', function ($row) {
                return number_format($row->amount, 2); 
            })
            ->editColumn('status', function ($row) {
                $badgeClass = '';
            
                switch ($row->status) {
                    case 'paid':
                        $badgeClass = 'badge-success'; 
                        break;
            
                    case 'unpaid':
                        $badgeClass = 'badge-warning'; 
                        break;
            
                    default:
                        break;
                }
            
                return '<span class="badge ' . $badgeClass . '">' . ucwords($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $action = '
                    <a href="' . route('admin.invoices.view', $row->id) . '" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> View
                    </a>
                ';

                // Add the "Add Payment" button only if the invoice status is unpaid
                if ($row->status == 'unpaid') {
                    $action .= '
                        <a href="' . route('admin.payments.add', $row->id) . '" class="btn btn-success btn-xs btn-flat">
                            <i class="fas fa-credit-card"></i> Add Payment
                        </a>
                    ';
                }

                return $action;
            })
            ->filterColumn('name', function ($query, $keyword) {
                UserNameSearch::applyForInvoicePersonName($query, $keyword);
            })
            ->filterColumn('package', function ($query, $keyword) {
                UserNameSearch::applyForInvoicePackageDisplay($query, $keyword);
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

}
