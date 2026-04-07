@extends('layouts.auth')

@section('title')
    @include('layouts.header', ['title' => 'Register'])
@endsection

@section('content')
    <div class="login-box container mt-5">
        <div class="login-logo">
            <a href="/"><b>{{ env('APP_NAME') }}</b></a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">{{ __('Register a new membership') }}</p>

                @if (session('error'))
                    <x-alert type="danger" icon="ban">
                        {{ session('error') }}
                    </x-alert>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="input-group">
                        <input id="first_name" placeholder="{{ __('First name') }}" type="text"
                               class="form-control @error('first_name') is-invalid @enderror" name="first_name"
                               value="{{ old('first_name') }}" required autocomplete="given-name" autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-user"></span></div>
                        </div>
                    </div>
                    @error('first_name')
                    <span class="text-danger" role="alert">{{ $message }}</span>
                    @enderror

                    <div class="input-group mt-3">
                        <input id="last_name" placeholder="{{ __('Last name') }}" type="text"
                               class="form-control @error('last_name') is-invalid @enderror" name="last_name"
                               value="{{ old('last_name') }}" required autocomplete="family-name">
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-user"></span></div>
                        </div>
                    </div>
                    @error('last_name')
                    <span class="text-danger" role="alert">{{ $message }}</span>
                    @enderror

                    <div class="input-group mt-3">
                        <input id="email" placeholder="{{ __('Email (optional)') }}" type="email"
                               class="form-control @error('email') is-invalid @enderror" name="email"
                               value="{{ old('email') }}" autocomplete="email">
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                        </div>
                    </div>
                    @error('email')
                    <span class="text-danger" role="alert">{{ $message }}</span>
                    @enderror

                    <div class="input-group mt-3">
                        <input id="phone" placeholder="{{ __('Mobile (07 or 09 + 8 digits)') }}" type="text"
                               class="form-control @error('phone') is-invalid @enderror" name="phone"
                               value="{{ old('phone') }}" required autocomplete="tel" maxlength="10" inputmode="numeric">
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-phone"></span></div>
                        </div>
                    </div>
                    @error('phone')
                    <span class="text-danger" role="alert">{{ $message }}</span>
                    @enderror

                    <div class="input-group mt-3">
                        <select id="gender" name="gender"
                                class="form-control @error('gender') is-invalid @enderror" required>
                            <option value="">{{ __('Gender') }}</option>
                            <option value="Female" @selected(old('gender') === 'Female')>{{ __('Female') }}</option>
                            <option value="Male" @selected(old('gender') === 'Male')>{{ __('Male') }}</option>
                        </select>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-venus-mars"></span></div>
                        </div>
                    </div>
                    @error('gender')
                    <span class="text-danger" role="alert">{{ $message }}</span>
                    @enderror

                    <div class="input-group mt-3">
                        <input id="password" placeholder="{{ __('Password') }}" type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               name="password" required autocomplete="new-password">
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                    </div>
                    @error('password')
                    <span class="text-danger" role="alert">{{ $message }}</span>
                    @enderror

                    <div class="input-group mt-3">
                        <input id="password_confirmation" placeholder="{{ __('Confirm password') }}" type="password"
                               class="form-control" name="password_confirmation" required autocomplete="new-password">
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block loading-button">
                                {{ __('Register') }}
                            </button>
                        </div>
                    </div>
                </form>
                <p class="mb-1 mt-3 text-center">
                    <a href="{{ route('login') }}">{{ __('Already registered?') }}</a>
                </p>
            </div>
        </div>
    </div>
@endsection
