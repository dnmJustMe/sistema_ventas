<?php    
define('NOMBRE_SERVIDOR', 'localhost' );
define('NOMBRE_USUARIO', 'root');
define('PASSWORD','');
define('NOMBRE_BD','sistema_ventas');

//rutas de la web
define("SERVIDOR", "http://localhost:8080/sistema_ventas");
define("RUTA_LOGIN", SERVIDOR."/login");
define("RUTA_LOGOUT", SERVIDOR. "/logout");
define("RUTA_DASHBOARD", SERVIDOR. "/dashboard");
define("RUTA_PRODUCTOS", SERVIDOR. "/productos");
define("RUTA_CATEGORIAS", SERVIDOR. "/categorias");
define("RUTA_SERVICIOS", SERVIDOR. "/servicios");
define("RUTA_OFERTAS", SERVIDOR. "/ofertas");





//recursos
define('RUTA_CSS', SERVIDOR. "/assets/css/");
define('RUTA_JS', SERVIDOR. "/assets/js/");
define('RUTA_IMG', SERVIDOR. "/assets/img/");
define('DIRECTORIO_RAIZ', realpath(__DIR__. "/.."));
