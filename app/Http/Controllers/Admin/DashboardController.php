<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Payment;
use App\Repositories\DashboardRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardRepository $dashboardRepository
    ) {
    }

    /**
     * Display home page
     *
     * @return View
     */
    public function index(): View
    {
        $summary = $this->dashboardRepository->getDashboardSummary();

        return view(self::ADMIN_.'index', compact('summary'));
    }

    /**
     * Download memberships as CSV (UTF-8).
     */
    public function exportMembershipsCsv(): StreamedResponse|RedirectResponse
    {
        try {
            $filename = 'memberships-'.now()->format('Y-m-d-His').'.csv';

            return response()->streamDownload(function () {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, [
                    'ID',
                    __('Member'),
                    __('Email'),
                    __('Phone'),
                    __('Start date'),
                    __('End date'),
                    __('Status'),
                    __('Price'),
                ]);

                Membership::query()
                    ->with(['user:id,first_name,last_name,email,phone'])
                    ->orderBy('id')
                    ->chunk(200, function ($chunk) use ($out) {
                        foreach ($chunk as $m) {
                            fputcsv($out, [
                                $m->id,
                                $m->user?->getName() ?? '',
                                $m->user?->email ?? '',
                                $m->user?->phone ?? '',
                                $m->start_date,
                                $m->end_date,
                                $m->status,
                                $m->price,
                            ]);
                        }
                    });
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        } catch (Throwable $e) {
            return redirect()->route('admin.home')->with(self::ERROR_, $e->getMessage());
        }
    }

    /**
     * Download completed payments as CSV (UTF-8).
     */
    public function exportPaymentsCsv(): StreamedResponse|RedirectResponse
    {
        try {
            $filename = 'payments-'.now()->format('Y-m-d-His').'.csv';

            return response()->streamDownload(function () {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, [
                    'ID',
                    __('Invoice'),
                    __('Member'),
                    __('Amount'),
                    __('Type'),
                    __('Method'),
                    __('Status'),
                    __('Payment date'),
                ]);

                Payment::query()
                    ->with(['invoice:id,invoice_number', 'membership.user:id,first_name,last_name'])
                    ->orderByDesc('payment_date')
                    ->chunk(200, function ($chunk) use ($out) {
                        foreach ($chunk as $p) {
                            fputcsv($out, [
                                $p->id,
                                $p->invoice?->invoice_number ?? '',
                                $p->membership?->user?->getName() ?? '',
                                $p->amount,
                                $p->payment_type ?? 'payment',
                                $p->payment_method,
                                $p->status,
                                $p->payment_date,
                            ]);
                        }
                    });
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        } catch (Throwable $e) {
            return redirect()->route('admin.home')->with(self::ERROR_, $e->getMessage());
        }
    }
}

