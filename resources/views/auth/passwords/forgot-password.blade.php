@extends('layouts.auth')
@section('title')
    @include('layouts.header', ['title' => 'Forget Password'])
@endsection
@section('content')
    <div class="login-box container mt-5">
        <div class="login-logo">
            <a href="/"><b>{{ env('APP_NAME') }}</b></a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">{{ __('Reset Password') }}</p>
                <p class="text-muted small mb-3">{{ __('Enter the mobile number on your account. We will email a reset link if that account has an email address.') }}</p>

                @if (session('status'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="input-group">
                        <input id="phone" type="text" placeholder="{{ __('07 or 09 + 8 digits') }}"
                               class="form-control @error('phone') is-invalid @enderror" name="phone"
                               value="{{ old('phone') }}" required maxlength="10" autocomplete="tel" inputmode="numeric" autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-phone"></span>
                            </div>
                        </div>
                    </div>
                    @error('phone')
                    <span class="text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror

                    <div class="row mt-3">
                        <div class="col-1"></div>
                        <div class="col-10">
                            <button type="submit" class="btn btn-primary btn-block loading-button">
                                {{ __('Send Password Reset Link') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
