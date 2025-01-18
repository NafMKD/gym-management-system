<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class AuditTrailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        try {
            $auditTrails = AuditTrail::orderBy('created_at', 'desc')->paginate(10);
            return view(self::ADMIN_.'audit-trail.list', compact('auditTrails'));
        } catch (Throwable $e) {
            dd($e);
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param AuditTrail $auditTrail
     * @return View|RedirectResponse
     */
    public function show(AuditTrail $auditTrail): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'audit-trail.view', compact('auditTrail'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Retrieves audit trail data from the database.
     *
     * @return JsonResponse
     */
    public function getTrailsData(): JsonResponse
    {
        $query = AuditTrail::query(); 

        return DataTables::of($query)
            ->addIndexColumn() 
            ->editColumn('user_id', function ($row) {
                return $row->user->getName() ?? 'N/A'; 
            })
            ->editColumn('table_name', function ($row) {
                return $row->table_name; 
            })
            ->editColumn('record_id', function ($row) {
                return $row->record_id; 
            })
            ->editColumn('action', function ($row) {
                $badges = [
                    'insert' => 'badge-success',
                    'update' => 'badge-primary',
                    'delete' => 'badge-danger'
                ];
                $badgeClass = $badges[$row->action] ?? 'badge-secondary';
                return '<span class="badge ' . $badgeClass . '">' . ucfirst($row->action) . '</span>';
            })
            ->addColumn('table_action', content: function ($row) {
                return '
                    <a href="' . route('admin.audit_trail.view', $row->id) . '" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> View
                    </a>
                ';
            })
            ->filterColumn('user_id', function ($query, $keyword) {
                $query->whereHas('user', function ($subQuery) use ($keyword) {
                    $subQuery->whereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$keyword}%"]);
                });
            })
            ->rawColumns(['action', 'table_action']) 
            ->make(true); 
    }

}
