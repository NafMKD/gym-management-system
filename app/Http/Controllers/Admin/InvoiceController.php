<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            return view(self::ADMIN_.'invoices.view', compact('invoice'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Retrieves user data from the database.
     *
     * @return  JsonResponse
     */
    public function getInvoicesData(): JsonResponse
    {
        $query = Invoice::query(); 

        return DataTables::of($query)
            ->addIndexColumn() 
            ->editColumn('name', function ($row) {
                return $row->membership->user->getName();
            })
            ->editColumn('package', function ($row) {
                return is_null($row->membership->package?->name) ? '-' :ucwords($row->membership->package?->name);
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
                        <a href="' . route('admin.invoices.list', $row->id) . '" class="btn btn-success btn-xs btn-flat">
                            <i class="fas fa-credit-card"></i> Add Payment
                        </a>
                    ';
                }

                return $action;
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

}
