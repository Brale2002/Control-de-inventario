<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body, html {
            margin: 0;
            padding: 0;
        }

        .dashboard-wrapper {
            display: flex;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background-color: #111827;
            min-height: 100vh;
            transition: transform 0.3s ease-in-out;
            transform: translateX(0); /* estado por defecto en escritorio */
        }
        
        
        /* Contenido */
        .dashboard-content {
            flex-grow: 1;
            padding: 25px;
            box-sizing: border-box;
        }
        
        .sidebar a { color: #9ca3af; text-decoration: none; }
        .sidebar a.active { background: #f3f4f6; color: #111827; font-weight: bold; }
        .mb-3 strong { color: #fff; }
        
        /* Botón menú móvil (estilo base) */
        .toggle-btn {
            position: fixed;
            top: 2px;   
            left: 5px;
            z-index: 1100;
            background: #0f1720;
            color: white;
            border: none;
            padding: 5px 10px;
            font-size: 20px;
            border-radius: 10px;
            transition: left 0.3s ease-in-out;
        }

        .toggle-btn.moved {
            left: 200px; /* o el ancho exacto del sidebar + margen */
        }
        
        @media (min-width: 769px) {
            .toggle-btn {
                display: none !important;
            }}
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            
            /* en mobile el sidebar inicialmente oculto */
            .sidebar {
                background-color: #111827;
                min-height: 100vh;
                position: fixed;
                top: 0;
                left: 0;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                z-index: 1100;
            }
        
            /* mostrar cuando tenga la clase "active" */
            .sidebar.active {
                transform: translateX(0);
            }
        
            .toggle-btn {
                display: block;
            }
        
            .toggle-hidden {
                transform: translateX(150px); /* mueve el botón a la derecha del sidebar (ajusta según el ancho del sidebar) */
                transition: transform 0.3s ease-in-out;
            }
        
            .dashboard-content {
                padding: 15px;
            }
        }
        
        /* overlay (opcional): se añade dinámicamente con JS si quieres oscurecer el fondo */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.35);
            z-index: 1090;
            display: none;
        }
        .sidebar-overlay.show { display: block; }
        </style>
        
</head>
<body>
<div class="dashboard-wrapper">

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column p-3" id="sidebar">

        <div class="mb-3 usu">
            <strong>{{ Auth::user()->nombre }}</strong>
            <div class="text-secondary" style="font-size: 0.9rem;">Usuario activo</div>
        </div>

        <hr class="text-secondary">

        <ul class="nav nav-pills flex-column mb-auto">
            @if (Auth::user()->rol === 'admin')
                <li class="nav-item mb-2">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('admin.usuarios') }}" class="nav-link {{ request()->routeIs('admin.usuarios') ? 'active' : '' }}">Usuarios</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('admin.inventario') }}" class="nav-link {{ request()->routeIs('admin.inventario') ? 'active' : '' }}">Cargar Inventario</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('admin.bodega.index') }}" class="nav-link {{ request()->routeIs('admin.bodega.index') ? 'active' : '' }}">Bodega</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('admin.ordenescompra.index') }}" class="nav-link {{ request()->routeIs('admin.ordenescompra.index') ? 'active' : '' }}">Ordenes de compra</a>
                </li>
            @endif

            @if (Auth::user()->rol !== 'admin')
                <li class="nav-item mb-2">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('propuestas.create') }}" class="nav-link {{ request()->routeIs('propuestas.create') ? 'active' : '' }}">Crear Cotización</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('propuestas.cotizacion') }}" class="nav-link {{ request()->routeIs('propuestas.cotizacion') ? 'active' : '' }}">Cotizaciones</a>
                </li>
            @endif
        </ul>

        <form action="{{ route('logout') }}" method="POST" class="mt-auto">
            @csrf
            <button type="submit" class="btn btn-outline-light w-100">Cerrar sesión</button>
        </form>
    </div>

    <!-- Contenido -->
    <div class="dashboard-content">
        @yield('content')
    </div>

</div>
<!-- Botón de menú móvil -->
<button class="toggle-btn" id="menu-toggle">☰</button>
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const toggleBtn = document.querySelector('.toggle-btn');
const sidebar = document.querySelector('.sidebar');

toggleBtn.addEventListener('click', () => {
    if (!sidebar.classList.contains('active')) {
        // Mostrar sidebar primero
        sidebar.classList.add('active');
        // Luego mover el botón (ligero retraso para fluidez)
        setTimeout(() => toggleBtn.classList.add('moved'), 10);
    } else {
        // Primero mover el botón de regreso
        toggleBtn.classList.remove('moved');
        // Luego ocultar el sidebar
        setTimeout(() => sidebar.classList.remove('active'), 10);
    }
});
</script>

@yield('scripts')
</body>
</html>
