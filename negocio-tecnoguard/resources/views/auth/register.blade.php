@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header text-center fs-4 fw-bold bg-white border-0">
                            {{ __('Crear Cuenta') }}
                        </div>

                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ __('Nombre Completo') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/account_circle_icon_black.svg') }}" alt="icono nombre" style="width:22px;height:22px;"></span>
                                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror border-start-0" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Juan Pérez">
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">{{ __('Email') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/mail_icon_black.svg') }}" alt="icono email" style="width:22px;height:22px;"></span>
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror border-start-0" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="example@gmail.com">
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
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror border-start-0" name="password" required autocomplete="new-password" placeholder="***********">
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password-confirm" class="form-label">{{ __('Confirmar Contraseña') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/key_icon_black.svg') }}" alt="icono confirmar contraseña" style="width:22px;height:22px;"></span>
                                        <input id="password-confirm" type="password" class="form-control border-start-0" name="password_confirmation" required autocomplete="new-password" placeholder="***********">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">{{ __('Teléfono') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/phone_icon_black.svg') }}" alt="icono teléfono" style="width:22px;height:22px;"></span>
                                        <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror border-start-0" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="5555555555">
                                        @error('phone')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="ine_front" class="form-label">{{ __('Foto de INE (Frente)') }}</label>
                                    <div class="input-group custom-input-icon">
                                        <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/camera_icon_black.svg') }}" alt="icono INE" style="width:22px;height:22px;"></span>
                                        <input id="ine_front" type="file" class="form-control @error('ine_front') is-invalid @enderror border-start-0" name="ine_front" required>
                                        @error('ine_front')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        {{ __('Registrarse') }}
                                    </button>
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                        {{ __('Iniciar Sesión') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 d-flex flex-column align-items-center justify-content-center p-4">
                    <img src="{{ asset('images/tecno-guard-logo.png') }}" alt="Tecno Guard Logo" class="img-fluid mb-4" style="max-width: 300px;">
                    <div class="card p-3 text-center border-0 shadow-sm">
                        <p class="mb-0 text-muted">Asegúrate que la imagen de tu INE sea clara.</p>
                        <p class="mb-0 text-muted mt-2">La autenticación de dos factores es obligatoria para mayor seguridad</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
