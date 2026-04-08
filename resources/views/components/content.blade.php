@props(['fluid' => false])

<section {{ $attributes }}>
    <div class="{{ $fluid ? 'container-fluid' : 'container' }}">
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
