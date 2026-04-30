@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Product'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Product') }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __('Home') }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.list') }}">{{ __('Products') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Detail') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    @php($canManageProducts = auth()->user()->role === 'admin')
    <x-content class="content">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">{{ $product->name }}</h3>
                @if($canManageProducts)
                    <div class="card-tools">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm">{{ __('Edit') }}</a>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">{{ __('SKU') }}</dt>
                    <dd class="col-sm-9">{{ $product->sku ?? '-' }}</dd>
                    <dt class="col-sm-3">{{ __('Unit price') }}</dt>
                    <dd class="col-sm-9">{{ number_format((float) $product->unit_price, 2) }}</dd>
                    <dt class="col-sm-3">{{ __('Stock') }}</dt>
                    <dd class="col-sm-9">
                        {{ $product->stock_quantity }}
                        @if($product->isLowStock())
                            <span class="badge badge-warning">{{ __('Low stock') }}</span>
                        @endif
                    </dd>
                    <dt class="col-sm-3">{{ __('Low stock threshold') }}</dt>
                    <dd class="col-sm-9">{{ $product->low_stock_threshold }}</dd>
                    <dt class="col-sm-3">{{ __('Active') }}</dt>
                    <dd class="col-sm-9">{{ $product->is_active ? __('Yes') : __('No') }}</dd>
                    <dt class="col-sm-3">{{ __('Description') }}</dt>
                    <dd class="col-sm-9">{{ $product->description ?: '-' }}</dd>
                </dl>

                <hr>
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <div>
                        <h5 class="mb-1">{{ __('Recent Quantity Log') }}</h5>
                        <p class="text-muted small mb-0">{{ __('Latest restocks, sales, and manual adjustments for this product.') }}</p>
                    </div>
                    <span class="badge badge-light">{{ __('Movements') }}: {{ number_format($product->stock_movements_count) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Reason') }}</th>
                                <th>{{ __('Quantity Change') }}</th>
                                <th>{{ __('Actor') }}</th>
                                <th>{{ __('Invoice') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentMovements as $movement)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($movement->created_at)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y H:i') }}</td>
                                    <td>{{ ucfirst($movement->reason) }}</td>
                                    <td class="{{ $movement->quantity_change < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($movement->quantity_change) }}</td>
                                    <td>{{ $movement->user?->getName() ?? __('Legacy / Unknown') }}</td>
                                    <td>{{ $movement->invoice?->invoice_number ?? '-' }}</td>
                                    <td>{{ $movement->invoice?->customer?->getName() ?? '-' }}</td>
                                    <td>{{ $movement->notes ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">{{ __('No stock movements recorded for this product yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($canManageProducts)
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ __('Restock') }}</h5>
                            <form method="POST" action="{{ route('admin.products.stock', $product) }}">
                                @csrf
                                <input type="hidden" name="stock_op" value="restock">
                                <div class="form-group">
                                    <label>{{ __('Quantity to add') }}</label>
                                    <input type="number" name="quantity" class="form-control" min="1" value="{{ old('stock_op') === 'restock' ? old('quantity') : '' }}" required>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Notes') }}</label>
                                    <input type="text" name="notes" class="form-control" maxlength="500" value="{{ old('stock_op') === 'restock' ? old('notes') : '' }}">
                                </div>
                                <button type="submit" class="btn btn-success">{{ __('Apply restock') }}</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('Adjustment') }}</h5>
                            <p class="text-muted small">{{ __('Use negative values for shrinkage (e.g. -2).') }}</p>
                            <form method="POST" action="{{ route('admin.products.stock', $product) }}">
                                @csrf
                                <input type="hidden" name="stock_op" value="adjustment">
                                <div class="form-group">
                                    <label>{{ __('Quantity change') }}</label>
                                    <input type="number" name="delta" class="form-control" value="{{ old('stock_op') === 'adjustment' ? old('delta') : '' }}" required>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Notes') }}</label>
                                    <input type="text" name="notes" class="form-control" maxlength="500" value="{{ old('stock_op') === 'adjustment' ? old('notes') : '' }}">
                                </div>
                                <button type="submit" class="btn btn-warning">{{ __('Apply adjustment') }}</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </x-content>
@endsection
