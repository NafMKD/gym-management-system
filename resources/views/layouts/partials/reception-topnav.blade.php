@foreach (config('reception.nav', []) as $item)
    <li class="nav-item">
        <a
            class="nav-link @if (request()->routeIs($item['active'])) active font-weight-bold @endif"
            href="{{ route($item['route']) }}"
        >
            @if (! empty($item['icon']))
                <i class="fas {{ $item['icon'] }} mr-1"></i>
            @endif
            {{ __($item['label']) }}
        </a>
    </li>
@endforeach
