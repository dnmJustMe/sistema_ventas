<?php
// app/scripts/ofertas.php

// Incluir archivos necesarios
include_once 'app/util/config.inc.php';
include_once 'app/util/Conexion.inc.php';
include_once 'app/util/ControlSesion.inc.php';

// Verificar sesión para operaciones que requieren autenticación
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo permitir acceso si hay sesión iniciada
if (!ControlSesion::sesion_iniciada()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit;
}

// Incluir repositorios
include_once 'app/repositorios/OfertaRepositorio.inc.php';
include_once 'app/repositorios/ProductoRepositorio.inc.php';
include_once 'app/repositorios/ImagenRepositorio.inc.php';
include_once 'app/entidades/Imagen.inc.php';
include_once 'app/entidades/Oferta.inc.php';

header('Content-Type: application/json');
// Iniciar buffer para evitar que cualquier "print/echo" rompa el JSON
if (ob_get_level() === 0) { ob_start(); }

// Configuración de subida de imágenes (igual que productos)
const IMG_MAX_BYTES = 5 * 1024 * 1024; // 5MB
const IMG_ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

function obtener_directorio_upload() {
    // app/scripts -> subir dos niveles al root del proyecto
    $root = dirname(__DIR__, 2);
    return rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR;
}

function asegurar_directorio_upload($dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return is_dir($dir) && is_writable($dir);
}

function generar_nombre_archivo($tipoEntidad, $entidadId, $extension) {
    try {
        $random = bin2hex(random_bytes(8));
    } catch (Exception $e) {
        $random = uniqid('', true);
    }
    return $tipoEntidad . '_' . intval($entidadId) . '_' . $random . '.' . $extension;
}

function validar_mime_y_ext($tmpName, $originalName) {
    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
    $mime = $finfo ? finfo_file($finfo, $tmpName) : null;
    if ($finfo) { finfo_close($finfo); }
    if ($mime && isset(IMG_ALLOWED_MIME[$mime])) {
        return [true, IMG_ALLOWED_MIME[$mime]];
    }
    // Fallback por extensión si no se pudo determinar MIME
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $map = array_flip(IMG_ALLOWED_MIME);
    if ($ext && isset($map[$ext])) {
        return [true, $ext];
    }
    return [false, null];
}

function procesar_subida_imagen_principal($conexion, $tipoEntidad, $entidadId) {
    if (!isset($_FILES['imagen_principal_file'])) {
        return [ 'insertadas' => 0, 'errores' => [] ];
    }
    $file = $_FILES['imagen_principal_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [ 'insertadas' => 0, 'errores' => ['Error al subir imagen principal'] ];
    }
    if ($file['size'] > IMG_MAX_BYTES) {
        return [ 'insertadas' => 0, 'errores' => ['Imagen principal supera 5MB'] ];
    }
    [$valido, $ext] = validar_mime_y_ext($file['tmp_name'], $file['name']);
    if (!$valido) {
        return [ 'insertadas' => 0, 'errores' => ['Tipo de archivo no permitido para principal'] ];
    }
    $uploadDir = obtener_directorio_upload();
    $okDir = asegurar_directorio_upload($uploadDir);
    if (!$okDir) {
        return [ 'insertadas' => 0, 'errores' => ['No se pudo preparar el directorio de subida'] ];
    }
    $filename = generar_nombre_archivo($tipoEntidad, $entidadId, $ext);
    $destino = $uploadDir . $filename;
    if (!@move_uploaded_file($file['tmp_name'], $destino)) {
        return [ 'insertadas' => 0, 'errores' => ['No se pudo guardar la imagen principal'] ];
    }
    
    // Insertar y marcar como principal; limpiar anteriores
    $campoId = $tipoEntidad . '_id';
    $conexion->prepare("UPDATE imagenes SET es_principal = 0 WHERE $campoId = :eid")
        ->execute([':eid' => $entidadId]);
        
    // Crear imagen según el tipo de entidad
    $imagen = new Imagen(null, 
        $tipoEntidad === 'producto' ? $entidadId : null,
        $tipoEntidad === 'oferta' ? $entidadId : null,
        $tipoEntidad === 'servicio' ? $entidadId : null,
        $filename, 
        null, 
        1
    );
    $ok = RepositorioImagen::insertar_imagen($conexion, $imagen);
    if ($ok) {
        return [ 'insertadas' => 1, 'errores' => [] ];
    } else {
        @unlink($destino);
        return [ 'insertadas' => 0, 'errores' => ['No se pudo registrar la imagen principal en BD'] ];
    }
}

function eliminar_imagenes_entidad($conexion, $tipoEntidad, $entidadId) {
    $campoId = $tipoEntidad . '_id';
    $uploadDir = obtener_directorio_upload();
    
    // Obtener imágenes antes de eliminar
    $stmt = $conexion->prepare("SELECT * FROM imagenes WHERE $campoId = :eid");
    $stmt->bindParam(':eid', $entidadId, PDO::PARAM_INT);
    $stmt->execute();
    $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Eliminar archivos físicos
    foreach ($imagenes as $img) {
        $file = $uploadDir . $img['path'];
        if (is_file($file)) {
            @unlink($file);
        }
    }
    
    // Eliminar registros de BD
    $stmt = $conexion->prepare("DELETE FROM imagenes WHERE $campoId = :eid");
    $stmt->bindParam(':eid', $entidadId, PDO::PARAM_INT);
    return $stmt->execute();
}

// Desactivar salida de errores en pantalla para no romper JSON
@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

// Asegurar salida JSON limpia
function send_json($payload, $status = 200) {
    http_response_code($status);
    while (ob_get_level() > 0) { @ob_end_clean(); }
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

try {
    $conexion = Conexion::obtener_conexion();
    
    // Leer datos de POST (ya que todas las peticiones son POST)
    $accion = $_POST['accion'] ?? 'listar';
    
    switch ($accion) {
        case 'listar':
            $busqueda = $_POST['busqueda'] ?? '';
            
            if (!empty($busqueda)) {
                $ofertas = RepositorioOferta::buscar_oferta_nombre($conexion, $busqueda);
            } else {
                $ofertas = RepositorioOferta::obtener_todos($conexion);
            }
            
            // Obtener información adicional para cada oferta
            $ofertasConInfo = [];
            foreach ($ofertas as $oferta) {
                // Obtener productos de la oferta
                $productos_con_cantidad = RepositorioOferta::obtener_productos_por_oferta($conexion, $oferta->obtener_id());
                // Obtener imagen principal
                $imagenPrincipal = RepositorioImagen::obtener_imagen_principal_por_entidad($conexion, 'oferta', $oferta->obtener_id());
                
                $ofertaArray = [
                    'id' => $oferta->obtener_id(),
                    'nombre' => $oferta->obtener_nombre(),
                    'descripcion' => $oferta->obtener_descripcion(),
                    'precio_final_venta' => $oferta->obtener_precio_final_venta(),
                    'fecha_creado' => $oferta->obtener_fecha_creado(),
                    'productos_count' => count($productos_con_cantidad),
                    'imagen_principal' => $imagenPrincipal ? $imagenPrincipal->obtener_path() : null
                ];
                $ofertasConInfo[] = $ofertaArray;
            }
            
            send_json([
                'success' => true,
                'data' => [
                    'ofertas' => $ofertasConInfo
                ]
            ]);
            
        case 'obtener':
            $id = $_POST['id'] ?? null;
            if ($id) {
                $oferta = RepositorioOferta::obtener_oferta_por_id($conexion, $id);
                if ($oferta) {
                    // Obtener productos de la oferta con cantidad
                    $productos_con_cantidad = RepositorioOferta::obtener_productos_por_oferta($conexion, $id);
                    $productosArray = [];
                    
                    foreach ($productos_con_cantidad as $item) {
                        $producto = $item['producto'];
                        $cantidad = $item['cantidad'];
                        
                        // Obtener imagen principal de cada producto
                        $imgProducto = RepositorioImagen::obtener_imagen_principal_por_entidad($conexion, 'producto', $producto->obtener_id());
                        
                        $productosArray[] = [
                            'id' => $producto->obtener_id(),
                            'nombre' => $producto->obtener_nombre(),
                            'descripcion' => $producto->obtener_descripcion(),
                            'precio_venta' => $producto->obtener_precio_venta(),
                            'cantidad' => $cantidad,
                            'imagen_principal' => $imgProducto ? $imgProducto->obtener_path() : null
                        ];
                    }
                    
                    // Obtener imagen principal de la oferta
                    $imagenPrincipal = RepositorioImagen::obtener_imagen_principal_por_entidad($conexion, 'oferta', $id);
                    
                    send_json([
                        'success' => true,
                        'data' => [
                            'oferta' => [
                                'id' => $oferta->obtener_id(),
                                'nombre' => $oferta->obtener_nombre(),
                                'descripcion' => $oferta->obtener_descripcion(),
                                'precio_final_venta' => $oferta->obtener_precio_final_venta(),
                                'fecha_creado' => $oferta->obtener_fecha_creado(),
                                'productos' => $productosArray,
                                'imagen_principal' => $imagenPrincipal ? $imagenPrincipal->obtener_path() : null
                            ]
                        ]
                    ]);
                } else {
                    send_json([
                        'success' => false,
                        'data' => [
                            'message' => 'Oferta no encontrada'
                        ]
                    ], 404);
                }
            } else {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'ID no especificado'
                    ]
                ], 400);
            }
            
        case 'crear':
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $precio_final_venta = $_POST['precio_final_venta'] ?? 0;
            $productos_json = $_POST['productos'] ?? '[]';
            
            // Validaciones
            if (empty($nombre)) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'El nombre es requerido'
                    ]
                ], 400);
            }
            
            if (RepositorioOferta::nombre_existe($conexion, $nombre)) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Ya existe una oferta con ese nombre'
                    ]
                ], 409);
            }
            
            $productos = json_decode($productos_json, true);
            if (empty($productos)) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'La oferta debe incluir al menos un producto'
                    ]
                ], 400);
            }
            
            // Crear oferta
            $oferta = new Oferta(null, $nombre, $descripcion, $precio_final_venta, null);
            $insertado = RepositorioOferta::insertar_oferta($conexion, $oferta);
            
            if ($insertado) {
                $ofertaId = $conexion->lastInsertId();
                
                // Procesar imagen principal
                $principalResult = procesar_subida_imagen_principal($conexion, 'oferta', $ofertaId);
                
                // Agregar productos a la oferta
                $productosAgregados = 0;
                foreach ($productos as $producto) {
                    if (RepositorioOferta::agregar_producto_a_oferta(
                        $conexion, 
                        $ofertaId, 
                        $producto['id'], 
                        $producto['cantidad']
                    )) {
                        $productosAgregados++;
                    }
                }
                
                // Validar que se haya subido imagen principal
                if ($principalResult['insertadas'] === 0) {
                    // Revertir la creación si no hay imagen
                    RepositorioOferta::eliminar_oferta($conexion, $ofertaId);
                    send_json([
                        'success' => false,
                        'data' => [ 'message' => 'Debe subir una imagen principal para la oferta' ]
                    ], 400);
                }
                
                send_json([
                    'success' => true,
                    'data' => [
                        'message' => 'Oferta creada exitosamente',
                        'productos_agregados' => $productosAgregados,
                        'errores_imagen' => $principalResult['errores']
                    ]
                ]);
            } else {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Error al crear la oferta'
                    ]
                ], 500);
            }
            
        case 'actualizar':
            $id = $_POST['id'] ?? null;
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $precio_final_venta = $_POST['precio_final_venta'] ?? 0;
            $productos_json = $_POST['productos'] ?? '[]';
            
            if (!$id) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'ID de oferta no especificado'
                    ]
                ], 400);
            }
            
            // Verificar si la oferta existe
            $ofertaExistente = RepositorioOferta::obtener_oferta_por_id($conexion, $id);
            if (!$ofertaExistente) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Oferta no encontrada'
                    ]
                ], 404);
            }
            
            // Verificar si el nombre ya existe (excluyendo la oferta actual)
            if ($nombre !== $ofertaExistente->obtener_nombre()) {
                if (RepositorioOferta::nombre_existe($conexion, $nombre)) {
                    send_json([
                        'success' => false,
                        'data' => [
                            'message' => 'Ya existe otra oferta con ese nombre'
                        ]
                    ], 409);
                }
            }
            
            $productos = json_decode($productos_json, true);
            if (empty($productos)) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'La oferta debe incluir al menos un producto'
                    ]
                ], 400);
            }
            
            $actualizado = RepositorioOferta::actualizar_oferta($conexion, $id, $nombre, $descripcion, $precio_final_venta);
            
            if ($actualizado) {
                // Procesar nueva imagen principal si se subió
                $principalResult = procesar_subida_imagen_principal($conexion, 'oferta', $id);
                
                // Actualizar productos: eliminar todos y agregar los nuevos
                RepositorioOferta::eliminar_productos_oferta($conexion, $id);
                
                $productosAgregados = 0;
                foreach ($productos as $producto) {
                    if (RepositorioOferta::agregar_producto_a_oferta(
                        $conexion, 
                        $id, 
                        $producto['id'], 
                        $producto['cantidad']
                    )) {
                        $productosAgregados++;
                    }
                }
                
                send_json([
                    'success' => true,
                    'data' => [
                        'message' => 'Oferta actualizada exitosamente',
                        'productos_agregados' => $productosAgregados,
                        'errores_imagen' => $principalResult['errores']
                    ]
                ]);
            } else {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Error al actualizar la oferta'
                    ]
                ], 500);
            }
            
        case 'eliminar':
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                send_json([
                    'success' => false,
                    'message' => 'ID de oferta no especificado'
                ], 400);
            }
            
            // Eliminar imágenes asociadas a la oferta
            eliminar_imagenes_entidad($conexion, 'oferta', $id);
            
            // Eliminar oferta (los productos se borran por FK ON DELETE CASCADE)
            $eliminado = RepositorioOferta::eliminar_oferta($conexion, $id);
            
            if ($eliminado) {
                send_json([
                    'success' => true,
                    'message' => 'Oferta eliminada exitosamente'
                ]);
            } else {
                send_json([
                    'success' => false,
                    'message' => 'Error al eliminar la oferta'
                ], 500);
            }
            
        default:
            send_json([
                'success' => false,
                'message' => 'Acción no válida: ' . $accion
            ], 400);
    }
    
} catch (Exception $e) {
    send_json([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ], 500);
}
?>