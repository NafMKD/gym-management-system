<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Repositories\PackageRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;
use App\Exceptions\NoUpdateNeededException;
use Illuminate\Http\JsonResponse;

class PackageController extends Controller
{

    public function __construct(
        protected PackageRepository $packageRepository
        )
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        try {
            $packages = Package::paginate(10);
            return view(self::ADMIN_.'packages.list', compact('packages'));
        } catch (Throwable $e) {
            return redirect()->back()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View|RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'packages.add');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only(['name', 'price', 'duration', 'description']);

        try {
            $this->packageRepository->store($attributes);
            return redirect()->back()->with(self::SUCCESS_, 'Package'.self::SUCCESS_STORE);;
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Package $package
     * @return View|RedirectResponse
     */
    public function show(Package $package): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'packages.view', compact('package'));
        } catch (Throwable $e) {
            dd($e);
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Package $package
     * @return View|RedirectResponse
     */
    public function edit(Package $package): View|RedirectResponse
    {
        try {
            return view(self::ADMIN_.'packages.edit', compact('package'));
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Package $package
     * @return RedirectResponse
     */
    public function update(Request $request, Package $package): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attributes = $request->only(['name', 'price', 'duration', 'description']);

        try {
            $this->packageRepository->update($package, $attributes);
            return redirect()->back()->withInput()->with(self::SUCCESS_, self::SUCCESS_UPDATE);
        } catch (NoUpdateNeededException $e) {
            return redirect()->back()->withInput()->with(self::SUCCESS_, self::SUCCESS_NO_UPDATE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Package $package
     * @return RedirectResponse
     */
    public function destroy(Package $package): RedirectResponse
    {
        try {
            $this->packageRepository->destroy($package);
            return redirect()->back()->withInput()->with(self::SUCCESS_, self::SUCCESS_DELETE);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with(self::ERROR_, self::ERROR_UNKNOWN);
        }
    }

    /**
     * Retrieves user data from the database.
     *
     * 
     */
    public function getPackagesData()
    {
        $query = Package::query(); 

        return DataTables::of($query)
            ->addIndexColumn() 
            ->editColumn('name', function ($row) {
                return $row->name;
            })
            ->editColumn('duration', function ($row) {
                return $row->duration;
            })
            ->editColumn('price', function ($row) {
                return $row->price; 
            })
            ->addColumn('action', function ($row) {
                return '
                    <a href="' . route('admin.packages.view', $row->id) . '" class="btn btn-info btn-xs btn-flat">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="' . route('admin.packages.edit', $row->id) . '" class="btn btn-primary btn-xs btn-flat">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="' . route('admin.packages.delete', $row->id) . '" 
                        onclick="if(confirm(\'Are you sure you want to delete ' . $row->name . '?\') == false){event.preventDefault()}" 
                        class="btn btn-danger btn-xs btn-flat">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Fetch package data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPackageData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $package = Package::find($validated['package_id']);

        return response()->json($package);
    }
}
