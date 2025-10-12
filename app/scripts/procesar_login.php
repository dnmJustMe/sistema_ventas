<?php
// Headers para CORS y JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Desactivar visualización de errores en producción
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Función para enviar respuesta JSON consistente
function sendJsonResponse($success, $message, $field = 'general', $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'field' => $field
    ]);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Método no permitido', 'general', 405);
}

try {
    // Incluir archivos necesarios
    $requiredFiles = [
        'app/util/Conexion.inc.php',
        'app/entidades/Usuario.inc.php',
        'app/repositorios/Usuariorepositorio.inc.php',
        'app/util/ControlSesion.inc.php'
    ];
    
    foreach ($requiredFiles as $file) {
        if (!file_exists($file)) {
            throw new Exception("Archivo requerido no encontrado: $file");
        }
        include_once $file;
    }

    // Verificar si ya hay una sesión activa
    if (ControlSesion::sesion_iniciada()) {
        sendJsonResponse(true, 'Ya hay una sesión activa', 'general', 200);
    }

    // Obtener y validar datos del POST
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $usuario = trim($input['usuario'] ?? '');
    $password = trim($input['password'] ?? '');

    // Validaciones básicas
    if (empty($usuario) || empty($password)) {
        sendJsonResponse(false, 'Usuario y contraseña son requeridos', 
                        empty($usuario) ? 'usuario' : 'password', 400);
    }

    // Conectar a la base de datos
    Conexion::abrir_conexion();
    $conexion = Conexion::obtener_conexion();

    if (!$conexion) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    // Buscar usuario por nombre
    $usuarioEncontrado = RepositorioUsuario::obtener_usuario_por_nombre($conexion, $usuario);

    if (!$usuarioEncontrado) {
        // Usuario no encontrado
        sendJsonResponse(false, 'Usuario o contraseña incorrectos', 'usuario', 401);
    }

    // Verificar contraseña
    $passwordBD = $usuarioEncontrado->obtener_password();
    
    if (password_verify($password, $passwordBD)) {
        // Credenciales correctas - Iniciar sesión
        ControlSesion::iniciar_sesion(
            $usuarioEncontrado->obtener_id(),
            $usuarioEncontrado->obtener_nombre()
        );

        echo json_encode([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'redirect' => 'dashboard',
            'user' => $usuarioEncontrado->obtener_nombre(),
            'user_id' => $usuarioEncontrado->obtener_id()
        ]);
    } else {
        // Contraseña incorrecta
        sendJsonResponse(false, 'Usuario o contraseña incorrectos', 'password', 401);
    }

    // Cerrar conexión
    Conexion::cerrar_conexion();

} catch (PDOException $ex) {
    // Error de base de datos
    error_log("Error de BD en login: " . $ex->getMessage());
    sendJsonResponse(false, 'Error en la base de datos. Por favor, intente más tarde.', 'general', 500);
} catch (Exception $e) {
    // Error general
    error_log("Error general en login: " . $e->getMessage());
    sendJsonResponse(false, 'Error interno del servidor: ' . $e->getMessage(), 'general', 500);
}
?>