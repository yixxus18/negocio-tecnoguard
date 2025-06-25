@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm card-depth">
                        <div class="card-header text-center fs-4 fw-bold bg-white border-0">
                            {{ __('Iniciar Sesión') }}
                        </div>

                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="email" class="form-label">{{ __('Email') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/mail_icon_black.svg') }}" alt="icono email" style="width:22px;height:22px;"></span>
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror border-start-0" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="example@gmail.com">
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">{{ __('Contraseña') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/key_icon_black.svg') }}" alt="icono contraseña" style="width:22px;height:22px;"></span>
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror border-start-0" name="password" required autocomplete="current-password" placeholder="***********">
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            {{ __('Recordarme') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 mb-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        {{ __('Iniciar Sesión') }}
                                    </button>
                                </div>

                                <div class="text-center">
                                    @if (Route::has('password.request'))
                                        <a class="btn btn-link" href="{{ route('password.request') }}">
                                            {{ __('¿Olvidaste tu contraseña?') }}
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-3 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <p class="mb-0">{{ __('¿No tienes una cuenta?') }}
                                <a href="{{ route('register') }}" class="btn btn-link p-0">{{ __('Regístrate aquí') }}</a>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 d-flex flex-column align-items-center justify-content-center p-4">
                    <img src="{{ asset('images/tecno-guard-logo.png') }}" alt="Tecno Guard Logo" class="img-fluid mb-4" style="max-width: 300px;">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
