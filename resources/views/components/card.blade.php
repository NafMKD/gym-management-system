<div {{ $attributes->merge(['class' => 'card']) }}>
    <div class="card-header d-flex align-items-center flex-wrap @if($attributes->has('sortable')) ui-sortable-handle @endif" @if($attributes->has('sortable')) style="cursor: move;" @endif>
        <h3 class="card-title mb-0">{{ __($title) }}</h3>
        @isset($headerTools)
            <div class="card-tools ml-auto text-right pl-2">
                {{ $headerTools }}
            </div>
        @endisset
    </div>
    @if($attributes->has('form')) <form method="POST" action="{{ route($attributes['form'], [$attributes->has('form-update') ? $attributes['form-update'] : null]) }}" id="{{ $attributes['form-id'] }}"> @csrf @endif
        <div class="card-body">
            @if(!$attributes->has('no-message'))
            @if(session('error'))
            <x-alert type="danger" icon="ban">
                {{ session('error') }}
            </x-alert>
            @endif

            @if(session('success'))
            <x-alert type="success" icon="check">
                {{ session('success') }}
            </x-alert>
            @endif
            @endif

            {{ $slot }}

        </div>
        @if($attributes->has('footer'))
            <div class="card-footer ">
                {{ $footer }}
            </div>
        @endif
    @if($attributes->has('form')) </form> @endif
</div>
