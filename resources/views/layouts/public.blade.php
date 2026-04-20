@php($brandName = 'My Fitness Gym')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', $brandName . ' is a premium training club with modern strength spaces, guided coaching, and a clean gym experience.')">
    <link rel="icon" href="{{ asset('assets/dist/img/favicon.ico') }}" type="image/x-icon">
    <title>@yield('title', $brandName)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/public-site.css') }}">
</head>
<body class="public-body">
    <div class="public-site">
        <header class="public-header">
            <div class="shell nav-shell">
                <a href="{{ route('home') }}" class="brand-mark" aria-label="Go to homepage">
                    <img src="{{ asset('assets/dist/img/logo.JPG') }}" alt="{{ $brandName }} logo" class="brand-logo">
                    <span class="brand-text-wrap">
                        <span class="brand-eyebrow">Private performance club</span>
                        <span class="brand-name">{{ $brandName }}</span>
                    </span>
                </a>

                <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="public-nav" data-menu-toggle>
                    <span></span>
                    <span></span>
                </button>

                <nav id="public-nav" class="public-nav" data-public-nav>
                    <a href="{{ request()->routeIs('home') ? '#experience' : route('home') . '#experience' }}">Experience</a>
                    <a href="{{ request()->routeIs('home') ? '#programs' : route('home') . '#programs' }}">Programs</a>
                    <a href="{{ request()->routeIs('home') ? '#packages' : route('home') . '#packages' }}">Packages</a>
                    <a href="{{ route('trainers') }}">Trainers</a>
                    <a href="{{ route('gallery') }}">Gallery</a>
                    <a href="{{ request()->routeIs('home') ? '#contact' : route('home') . '#contact' }}">Contact</a>
                    @auth
                        <a class="nav-pill" href="{{ route('dashboard') }}">Dashboard</a>
                    @else
                        <a class="nav-pill" href="{{ route('login') }}">Member Login</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="public-footer">
            <div class="shell footer-grid">
                <div>
                    <img src="{{ asset('assets/dist/img/logo.JPG') }}" alt="{{ $brandName }} logo" class="footer-logo">
                    <p class="footer-kicker">Train beyond ordinary</p>
                    <h2>{{ $brandName }}</h2>
                    <p class="footer-copy">Elegant strength spaces, guided coaching, and a premium gym rhythm.</p>
                </div>
                <div>
                    <p class="footer-title">Explore</p>
                    <a href="{{ route('home') }}">Home</a>
                    <a href="{{ route('trainers') }}">Trainers</a>
                    <a href="{{ route('gallery') }}">Gallery</a>
                </div>
                <div id="contact">
                    <p class="footer-title">Visit</p>
                    <p>24/7 access for members</p>
                    <p>Bole Skyline District</p>
                    <p>Addis Ababa, Ethiopia</p>
                </div>
                <div>
                    <p class="footer-title">Contact</p>
                    <a href="tel:+251900000000">+251 90 000 0000</a>
                    <a href="mailto:hello@myfitnessclub.com">hello@myfitnessclub.com</a>
                    <a href="{{ route('login') }}">Member Login</a>
                </div>
            </div>
        </footer>
    </div>
    <script src="{{ asset('assets/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/dist/js/public-site.js') }}"></script>
</body>
</html>
