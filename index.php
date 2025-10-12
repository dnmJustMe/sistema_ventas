<?php
include_once 'app/util/config.inc.php';
include_once 'app/util/Conexion.inc.php';
include_once 'app/util/ControlSesion.inc.php';
include_once 'app/util/Redireccion.inc.php';

// Iniciar sesión para verificar estado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$componentes_url = parse_url($_SERVER["REQUEST_URI"]);
$ruta = $componentes_url['path'];
$partes_ruta = explode('/', $ruta);
$partes_ruta = array_filter($partes_ruta);
$partes_ruta = array_slice($partes_ruta, 0);

// Determinar si es una petición AJAX/API
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$ruta_elegida = 'vistas/404.php';
$sesionIniciada = ControlSesion::sesion_iniciada();

if ($partes_ruta[0] == 'sistema_ventas') {
    if (count($partes_ruta) == 1) {
        $ruta_elegida = 'vistas/home.php';
    } else if (count($partes_ruta) == 2) {
        switch ($partes_ruta[1]) {
            case 'login':
                // Si ya hay sesión y va al login → redirigir al dashboard
                if ($sesionIniciada) {
                    Redireccion::redirigir(SERVIDOR . '/dashboard');
                }
                $ruta_elegida = 'vistas/login.php';
                break;

            case 'dashboard':
                // Si no hay sesión y va al dashboard → redirigir al login
                if (!$sesionIniciada) {
                    Redireccion::redirigir(SERVIDOR . '/login');
                }
                $ruta_elegida = 'vistas/dashboard.php';
                break;

            case 'logout':
                $ruta_elegida = 'app/scripts/logout.php';
                break;

            case 'productos':
                // Si no hay sesión y va a productos → redirigir al login
                if (!$sesionIniciada) {
                    Redireccion::redirigir(SERVIDOR . '/login');
                }
                $ruta_elegida = 'vistas/productos.php';
                break;

            case 'categorias':
                // Si no hay sesión y va a categorias → redirigir al login
                if (!$sesionIniciada) {
                    Redireccion::redirigir(SERVIDOR . '/login');
                }
                $ruta_elegida = 'vistas/categorias.php';
                break;

            // Rutas para AJAX - IMPORTANTE: usar exit después de incluir
            case 'get_productos':
                include_once 'app/scripts/productos.php';
                exit;
                break;

            case 'get_categorias':
                include_once 'app/scripts/categorias.php';
                exit;
                break;

            case 'crear_producto':
                include_once 'app/scripts/productos.php';
                exit;
                break;

            case 'actualizar_producto':
                include_once 'app/scripts/productos.php';
                exit;
                break;

            case 'eliminar_producto':
                include_once 'app/scripts/productos.php';
                exit;
                break;

            case 'get_producto':
                include_once 'app/scripts/productos.php';
                exit;
                break;

            case 'crear_categoria':
                include_once 'app/scripts/categorias.php';
                exit;
                break;

            case 'actualizar_categoria':
                include_once 'app/scripts/categorias.php';
                exit;
                break;

            case 'eliminar_categoria':
                include_once 'app/scripts/categorias.php';
                exit;
                break;

            case 'get_categoria':
                include_once 'app/scripts/categorias.php';
                exit;
                break;

            case 'procesar_login':
                include_once 'app/scripts/procesar_login.php';
                exit;
                break;
        }
    }
}

// Solo incluir la vista si no es una petición AJAX
if (!$isAjax) {
    include_once $ruta_elegida;
}