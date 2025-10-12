<?php
// app/scripts/productos.php

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
include_once 'app/repositorios/ProductoRepositorio.inc.php';
include_once 'app/repositorios/CategoriaRepositorio.inc.php';
include_once 'app/repositorios/ImagenRepositorio.inc.php';
include_once 'app/entidades/Imagen.inc.php';

header('Content-Type: application/json');
// Iniciar buffer para evitar que cualquier "print/echo" rompa el JSON
if (ob_get_level() === 0) { ob_start(); }

// Configuración de subida de imágenes
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

function generar_nombre_archivo($productoId, $extension) {
    try {
        $random = bin2hex(random_bytes(8));
    } catch (Exception $e) {
        $random = uniqid('', true);
    }
    return 'prod_' . intval($productoId) . '_' . $random . '.' . $extension;
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

function procesar_subida_imagenes($conexion, $productoId) {
    if (!isset($_FILES['imagenes'])) {
        return [ 'insertadas' => 0, 'errores' => [] ];
    }

    $uploadDir = obtener_directorio_upload();
    $okDir = asegurar_directorio_upload($uploadDir);
    if (!$okDir) {
        return [ 'insertadas' => 0, 'errores' => ['No se pudo preparar el directorio de subida'] ];
    }

    $names = $_FILES['imagenes']['name'];
    $tmpNames = $_FILES['imagenes']['tmp_name'];
    $sizes = $_FILES['imagenes']['size'];
    $errors = $_FILES['imagenes']['error'];

    $n = is_array($names) ? count($names) : 0;
    $errores = [];
    $ok = 0;

    for ($i = 0; $i < $n; $i++) {
        if ($errors[$i] !== UPLOAD_ERR_OK) {
            $errores[] = 'Error al subir archivo ' . ($names[$i] ?? '');
            continue;
        }
        if ($sizes[$i] > IMG_MAX_BYTES) {
            $errores[] = 'Archivo demasiado grande: ' . ($names[$i] ?? '') . ' (máx 5MB)';
            continue;
        }
        [$valido, $ext] = validar_mime_y_ext($tmpNames[$i], $names[$i]);
        if (!$valido) {
            $errores[] = 'Tipo de archivo no permitido: ' . ($names[$i] ?? '');
            continue;
        }

        $filename = generar_nombre_archivo($productoId, $ext);
        $destino = $uploadDir . $filename;

        if (!@move_uploaded_file($tmpNames[$i], $destino)) {
            $errores[] = 'No se pudo guardar el archivo: ' . $names[$i];
            continue;
        }

        // Guardar en BD solo el nombre del archivo
        $esPrincipal = 0;
        // Marcar como principal si viene marcado en formulario (por índice)
        if (isset($_POST['imagen_principal'])) {
            // valores aceptados: index del file o 'nuevo' cuando viene de drag-drop
            $principalIndex = $_POST['imagen_principal'];
            if ($principalIndex !== '' && strval($principalIndex) === strval($i)) {
                $esPrincipal = 1;
            }
        }
        $imagen = new Imagen(null, $productoId, $filename, null, $esPrincipal);
        $insertada = RepositorioImagen::insertar_imagen($conexion, $imagen);
        if ($insertada) {
            $ok++;
        } else {
            // Revertir archivo en disco si falla BD
            @unlink($destino);
            $errores[] = 'No se pudo registrar la imagen en BD: ' . $names[$i];
        }
    }

    return [ 'insertadas' => $ok, 'errores' => $errores ];
}

function procesar_subida_imagen_principal($conexion, $productoId) {
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
    $filename = generar_nombre_archivo($productoId, $ext);
    $destino = $uploadDir . $filename;
    if (!@move_uploaded_file($file['tmp_name'], $destino)) {
        return [ 'insertadas' => 0, 'errores' => ['No se pudo guardar la imagen principal'] ];
    }
    // Insertar y marcar como principal; limpiar anteriores
    $conexion->prepare("UPDATE imagenes SET es_principal = 0 WHERE producto_id = :pid")
        ->execute([':pid' => $productoId]);
    $imagen = new Imagen(null, $productoId, $filename, null, 1);
    $ok = RepositorioImagen::insertar_imagen($conexion, $imagen);
    if ($ok) {
        return [ 'insertadas' => 1, 'errores' => [] ];
    } else {
        @unlink($destino);
        return [ 'insertadas' => 0, 'errores' => ['No se pudo registrar la imagen principal en BD'] ];
    }
}

function contar_imagenes_producto($conexion, $productoId) {
    $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM imagenes WHERE producto_id = :pid");
    $stmt->bindParam(':pid', $productoId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
}

function unificar_principal($conexion, $productoId, $preferId = null) {
    if ($preferId) {
        // Forzar la imagen indicada como principal
        $conexion->prepare("UPDATE imagenes SET es_principal = 0 WHERE producto_id = :pid")
            ->execute([':pid' => $productoId]);
        $stmt = $conexion->prepare("UPDATE imagenes SET es_principal = 1 WHERE id = :id AND producto_id = :pid");
        $stmt->bindParam(':id', $preferId, PDO::PARAM_INT);
        $stmt->bindParam(':pid', $productoId, PDO::PARAM_INT);
        $stmt->execute();
        return;
    }

    // Si ya hay imágenes marcadas como principal, dejar solo una y limpiar el resto
    $stmt = $conexion->prepare("SELECT id FROM imagenes WHERE producto_id = :pid AND es_principal = 1 ORDER BY id DESC");
    $stmt->bindParam(':pid', $productoId, PDO::PARAM_INT);
    $stmt->execute();
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    if (!empty($ids)) {
        $keepId = (int)$ids[0];
        if (count($ids) > 1) {
            // Poner en 0 todas excepto la elegida
            $in = implode(',', array_map('intval', array_slice($ids, 1)));
            if ($in !== '') {
                $conexion->exec("UPDATE imagenes SET es_principal = 0 WHERE producto_id = " . (int)$productoId . " AND id IN (" . $in . ")");
            }
        }
        return;
    }

    // Si ninguna está marcada, establecer la más reciente como principal
    $stmt = $conexion->prepare("SELECT id FROM imagenes WHERE producto_id = :pid ORDER BY id DESC LIMIT 1");
    $stmt->bindParam(':pid', $productoId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    if (!empty($row)) {
        $id = (int)$row['id'];
        $stmt = $conexion->prepare("UPDATE imagenes SET es_principal = 1 WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
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
                $productos = RepositorioProducto::buscar_producto_nombre($conexion, $busqueda);
            } else {
                $productos = RepositorioProducto::obtener_todos($conexion);
            }
            
            // Obtener nombres de categorías para cada producto
            $productosConCategorias = [];
            foreach ($productos as $producto) {
                $categoria = RepositorioCategoria::obtener_categoria_por_id($conexion, $producto->obtener_categoria_id());
                $productoArray = [
                    'id' => $producto->obtener_id(),
                    'nombre' => $producto->obtener_nombre(),
                    'descripcion' => $producto->obtener_descripcion(),
                    'costo' => $producto->obtener_costo(),
                    'precio_venta' => $producto->obtener_precio_venta(),
                    'categoria_id' => $producto->obtener_categoria_id(),
                    'categoria_nombre' => $categoria ? $categoria->obtener_nombre() : 'Sin categoría',
                    'fecha_creado' => $producto->obtener_fecha_creado()
                ];
                $productosConCategorias[] = $productoArray;
            }
            
            send_json([
                'success' => true,
                'data' => [
                    'productos' => $productosConCategorias
                ]
            ]);
            
        case 'obtener':
            $id = $_POST['id'] ?? null;
            if ($id) {
                $producto = RepositorioProducto::obtener_producto_por_id($conexion, $id);
                if ($producto) {
                    // Obtener imágenes del producto
                    $imgs = RepositorioImagen::obtener_imagenes_por_producto($conexion, $id);
                    $imagenes = [];
                    if (!empty($imgs)) {
                        foreach ($imgs as $img) {
                            $imagenes[] = [
                                'id' => $img->obtener_id(),
                                'path' => $img->obtener_path(), // nombre de archivo
                                'es_principal' => $img->obtener_es_principal(),
                            ];
                        }
                    }
                    $categoria = RepositorioCategoria::obtener_categoria_por_id($conexion, $producto->obtener_categoria_id());
                    send_json([
                        'success' => true,
                        'data' => [
                            'producto' => [
                                'id' => $producto->obtener_id(),
                                'nombre' => $producto->obtener_nombre(),
                                'descripcion' => $producto->obtener_descripcion(),
                                'costo' => $producto->obtener_costo(),
                                'precio_venta' => $producto->obtener_precio_venta(),
                                'categoria_id' => $producto->obtener_categoria_id(),
                                'categoria_nombre' => $categoria ? $categoria->obtener_nombre() : 'Sin categoría',
                                'fecha_creado' => $producto->obtener_fecha_creado(),
                                'imagenes' => $imagenes,
                            ]
                        ]
                    ]);
                } else {
                    send_json([
                        'success' => false,
                        'data' => [
                            'message' => 'Producto no encontrado'
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
            $costo = $_POST['costo'] ?? 0;
            $precio_venta = $_POST['precio_venta'] ?? 0;
            $categoria_id = $_POST['categoria_id'] ?? null;
            
            // Validaciones
            if (empty($nombre)) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'El nombre es requerido'
                    ]
                ], 400);
            }
            
            if (RepositorioProducto::nombre_existe($conexion, $nombre)) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Ya existe un producto con ese nombre'
                    ]
                ], 409);
            }
            
            $producto = new Producto(null, $nombre, $descripcion, $costo, $precio_venta, $categoria_id, null);
            $insertado = RepositorioProducto::insertar_producto($conexion, $producto);
            
            if ($insertado) {
                // ID del producto insertado
                $productoId = $conexion->lastInsertId();
                // Procesar imagen principal (obligatoria en creación)
                $principalResult = procesar_subida_imagen_principal($conexion, $productoId);
                // Procesar imágenes adicionales si llegaron
                $resultadoUpload = procesar_subida_imagenes($conexion, $productoId);
                // Validar que haya al menos 1 imagen y una principal
                $total = contar_imagenes_producto($conexion, $productoId);
                if ($total === 0 || $principalResult['insertadas'] === 0) {
                    send_json([
                        'success' => false,
                        'data' => [ 'message' => 'Debe subir al menos una imagen del producto' ]
                    ], 400);
                }
                unificar_principal($conexion, $productoId);
                send_json([
                    'success' => true,
                    'data' => [
                        'message' => 'Producto creado exitosamente',
                        'imagenes_insertadas' => $resultadoUpload['insertadas'] + $principalResult['insertadas'],
                        'errores_imagenes' => array_merge($resultadoUpload['errores'], $principalResult['errores'])
                    ]
                ]);
            } else {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Error al crear el producto'
                    ]
                ], 500);
            }
            
            
        case 'actualizar':
            $id = $_POST['id'] ?? null;
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $costo = $_POST['costo'] ?? 0;
            $precio_venta = $_POST['precio_venta'] ?? 0;
            $categoria_id = $_POST['categoria_id'] ?? null;
            
            if (!$id) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'ID de producto no especificado'
                    ]
                ], 400);
            }
            
            // Verificar si el producto existe
            $productoExistente = RepositorioProducto::obtener_producto_por_id($conexion, $id);
            if (!$productoExistente) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Producto no encontrado'
                    ]
                ], 404);
            }
            
            // Verificar si el nombre ya existe (excluyendo el producto actual)
            if ($nombre !== $productoExistente->obtener_nombre()) {
                if (RepositorioProducto::nombre_existe($conexion, $nombre)) {
                    send_json([
                        'success' => false,
                        'data' => [
                            'message' => 'Ya existe otro producto con ese nombre'
                        ]
                    ], 409);
                }
            }
            
            $actualizado = RepositorioProducto::actualizar_producto($conexion, $id, $nombre, $descripcion, $costo, $precio_venta, $categoria_id);
            
            if ($actualizado) {
                // Si subieron imagen principal nueva, procesarla (reemplaza principal)
                $principalResult = procesar_subida_imagen_principal($conexion, $id);

                // Eliminar imágenes solicitadas (si hay)
                if (!empty($_POST['imagenes_eliminar'])) {
                    $idsEliminar = array_filter(array_map('intval', explode(',', $_POST['imagenes_eliminar'])));
                    if (!empty($idsEliminar)) {
                        $uploadDir = obtener_directorio_upload();
                        $existentes = RepositorioImagen::obtener_imagenes_por_producto($conexion, $id);
                        $mapa = [];
                        foreach ($existentes as $img) { $mapa[$img->obtener_id()] = $img; }
                        foreach ($idsEliminar as $imgId) {
                            if (isset($mapa[$imgId])) {
                                $file = $uploadDir . $mapa[$imgId]->obtener_path();
                                if (is_file($file)) { @unlink($file); }
                                RepositorioImagen::eliminar_imagen($conexion, $imgId);
                            }
                        }
                    }
                }

                // Subir nuevas imágenes si llegaron
                $resultadoUpload = procesar_subida_imagenes($conexion, $id);

                // Determinar y fijar principal
                $preferId = null;
                if (!empty($_POST['imagen_principal_existente']) && ($principalResult['insertadas'] ?? 0) === 0) {
                    $preferId = (int)$_POST['imagen_principal_existente'];
                }
                unificar_principal($conexion, $id, $preferId);

                send_json([
                    'success' => true,
                    'data' => [
                        'message' => 'Producto actualizado exitosamente',
                        'imagenes_insertadas' => $resultadoUpload['insertadas'] + ($principalResult['insertadas'] ?? 0),
                        'errores_imagenes' => array_merge($resultadoUpload['errores'], $principalResult['errores'] ?? [])
                    ]
                ]);
            } else {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Error al actualizar el producto'
                    ]
                ], 500);
            }
            
            
        case 'eliminar':
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                send_json([
                    'success' => false,
                    'message' => 'ID de producto no especificado'
                ], 400);
            }
            // Recopilar imágenes antes de eliminar en BD para poder borrar archivos
            $imagenesAntes = RepositorioImagen::obtener_imagenes_por_producto($conexion, $id);
            $eliminado = RepositorioProducto::eliminar_producto($conexion, $id);
            
            if ($eliminado) {
                // Borrar archivos en disco (las filas se borran por FK ON DELETE CASCADE)
                $uploadDir = obtener_directorio_upload();
                foreach ($imagenesAntes as $img) {
                    $file = $uploadDir . $img->obtener_path();
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
                send_json([
                    'success' => true,
                    'message' => 'Producto eliminado exitosamente'
                ]);
            } else {
                send_json([
                    'success' => false,
                    'message' => 'Error al eliminar el producto'
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