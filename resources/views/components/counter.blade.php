<div {{ $attributes->merge(['class' => 'info-box']) }}>
    <span class="info-box-icon bg-{{ $attributes['type'] }} elevation-1"><i class="{{ $attributes['icon'] }}"></i></span>

    <div class="info-box-content">
        <span class="info-box-text">{{ $slot }}</span>
        <span class="info-box-number">
            {{ $count ?? '' }}
        </span>
    </div>
</div>
