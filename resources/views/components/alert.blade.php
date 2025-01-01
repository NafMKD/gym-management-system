<div class="alert alert-{{ $type }} alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert"
            aria-hidden="true">×</button>
    <i class="icon fas fa-{{ $icon }}"></i>
    {{ $slot }}
</div>
