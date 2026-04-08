@foreach (config('reception.nav', []) as $item)
    @if (($item['type'] ?? 'link') === 'dropdown')
        @php
            $dropdownActive = ! empty($item['active']) && request()->routeIs($item['active']);
        @endphp
        <li class="nav-item dropdown">
            <a
                class="nav-link dropdown-toggle py-1 @if ($dropdownActive) active @endif"
                href="#"
                id="receptionDeskDropdown"
                role="button"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
            >
                {{ __($item['label']) }}
            </a>
            <div class="dropdown-menu reception-nav-dropdown" aria-labelledby="receptionDeskDropdown">
                @foreach ($item['items'] ?? [] as $sub)
                    <a
                        class="dropdown-item @if (request()->routeIs($sub['active'])) active @endif"
                        href="{{ route($sub['route']) }}"
                    >
                        {{ __($sub['label']) }}
                    </a>
                @endforeach
            </div>
        </li>
    @else
        <li class="nav-item">
            <a
                class="nav-link py-1 @if (request()->routeIs($item['active'])) active font-weight-bold @endif"
                href="{{ route($item['route']) }}"
            >
                {{ __($item['label']) }}
            </a>
        </li>
    @endif
@endforeach
