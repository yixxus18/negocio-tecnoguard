@extends('layouts.app')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="row w-100 justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm p-4 text-center">
                <h2 class="fw-bold mb-4">Codigo</h2>
                <form method="POST" action="{{ route('2fa.verify') }}">
                    @csrf
                    <div class="mb-4 text-start">
                        <label for="two_factor_code" class="form-label fw-bold">Codigo de verificación</label>
                        <div class="input-group custom-input-icon">
                            <span class="input-group-text bg-white border-end-0 p-0 ps-2 pe-1"><img src="{{ asset('images/black/key_icon_black.svg') }}" alt="icono llave" style="width:22px;height:22px;"></span>
                            <input id="two_factor_code" type="text" class="form-control @error('two_factor_code') is-invalid @enderror border-start-0" name="two_factor_code" required autofocus placeholder="000000">
                            @error('two_factor_code')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">Aceptar</button>
                </form>
            </div>
        </div>
        <div class="col-md-6 d-flex flex-column align-items-center justify-content-center p-4">
            <img src="{{ asset('images/tecno-guard-logo.png') }}" alt="Tecno Guard Logo" class="img-fluid mb-4" style="max-width: 300px;">
        </div>
    </div>
</div>
@endsection
