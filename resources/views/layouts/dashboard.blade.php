@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: var(--background-color) !important; /* Asegura el color de fondo para el dashboard */
    }
    .dashboard-wrapper {
        display: flex;
        min-height: calc(100vh - 56px); /* Altura de la barra de navegación */
    }
    .sidebar {
        width: 250px;
        background-color: var(--third-color); /* Color de la barra lateral */
        color: var(--white-color);
        padding: 20px;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .sidebar .logo-section {
        text-align: center;
        margin-bottom: 30px;
    }
    .sidebar .logo-section img {
        max-width: 80px;
        margin-bottom: 10px;
    }
    .sidebar .logo-section h2 {
        color: var(--white-color);
        font-size: 1.2rem;
        font-weight: 700;
    }
    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.8);
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 10px;
        transition: background-color 0.3s ease, color 0.3s ease;
        display: flex;
        align-items: center;
    }
    .sidebar .nav-link.active,
    .sidebar .nav-link:hover {
        background-color: var(--primary-color); /* Color primario para activo/hover */
        color: var(--white-color);
    }
    .sidebar .nav-link i {
        margin-right: 10px;
    }
    .main-content {
        flex-grow: 1;
        padding: 30px;
        background-color: var(--background-color);
    }
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .dashboard-header h1 {
        font-size: 2rem;
        color: var(--black-color);
        font-weight: 600;
    }
    .user-profile-circle {
        width: 40px;
        height: 40px;
        background-color: var(--secondary-color); /* Color secundario para el círculo de usuario */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white-color);
        font-weight: bold;
        font-size: 1rem;
    }
    .card-dashboard {
        background-color: var(--white-color);
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        text-align: center;
        color: var(--black-color);
    }
    .card-dashboard .icon {
        font-size: 2.5rem;
        color: var(--primary-color);
        margin-bottom: 10px;
    }
    .card-dashboard h4 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 5px;
    }
    .card-dashboard p {
        color: var(--gray-color);
        margin-bottom: 0;
    }

    /* Tabla */
    .table-container {
        background-color: var(--white-color);
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        margin-top: 30px;
    }
    .table th,
    .table td {
        vertical-align: middle;
    }
    .table thead th {
        background-color: var(--secondary-color); /* Color secundario para el encabezado de la tabla */
        color: var(--white-color);
        border-bottom: none;
    }
    .table tbody tr:hover {
        background-color: #f1f1f1;
    }
    .status-active {
        color: green;
        font-weight: 500;
    }
    .status-inactive {
        color: red;
        font-weight: 500;
    }
    .btn-view-more {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: var(--white-color);
        transition: background-color 0.3s ease;
    }
    .btn-view-more:hover {
        background-color: var(--third-color);
        border-color: var(--third-color);
    }
</style>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('images/tecno-guard-logo-white.png') }}" alt="Tecno Guard Logo">
            <h2>TECNO GUARD</h2>
            <p class="text-white-50">SISTEMA DE CONTROL DE ACCESO</p>
        </div>
        <ul class="nav flex-column w-100">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('home') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-users"></i> Gestión de usuarios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-door-open"></i> Gestión de cerradas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-money-bill-wave"></i> Gestión de pagos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-key"></i> Tokens de acceso
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-cog"></i> Configuración
                </a>
            </li>
            <li class="nav-item mt-auto">
                <a class="nav-link" href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                   document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @yield('dashboard_content')
    </div>
</div>
@endsection
