@props(['fluid' => null])

@php
    $useFluid = $fluid;
    if ($useFluid === null) {
        // Reception desk: constrained width; admin and other roles: full-width like original AdminLTE.
        $useFluid = ! (auth()->check() && auth()->user()->role === 'reception');
    }
@endphp

<section {{ $attributes }}>
    <div class="{{ $useFluid ? 'container-fluid' : 'container' }}">
        @if($attributes->has('sortable'))
            <div class="row">
                <section class="col-lg-12 connectedSortable ui-sortable">
        @endif
        {{ $slot }}
        @if($attributes->has('sortable'))
                </section>
            </div>
        @endif
    </div>
</section>
