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
        $imagen = new Imagen(null, $productoId, $filename, null);
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
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'productos' => $productosConCategorias
                ]
            ]);
            break;
            
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
                            ];
                        }
                    }
                    $categoria = RepositorioCategoria::obtener_categoria_por_id($conexion, $producto->obtener_categoria_id());
                    echo json_encode([
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
                    echo json_encode([
                        'success' => false,
                        'data' => [
                            'message' => 'Producto no encontrado'
                        ]
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => 'ID no especificado'
                    ]
                ]);
            }
            break;
            
        case 'crear':
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $costo = $_POST['costo'] ?? 0;
            $precio_venta = $_POST['precio_venta'] ?? 0;
            $categoria_id = $_POST['categoria_id'] ?? null;
            
            // Validaciones
            if (empty($nombre)) {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => 'El nombre es requerido'
                    ]
                ]);
                break;
            }
            
            if (RepositorioProducto::nombre_existe($conexion, $nombre)) {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => 'Ya existe un producto con ese nombre'
                    ]
                ]);
                break;
            }
            
            $producto = new Producto(null, $nombre, $descripcion, $costo, $precio_venta, $categoria_id, null);
            $insertado = RepositorioProducto::insertar_producto($conexion, $producto);
            
            if ($insertado) {
                // ID del producto insertado
                $productoId = $conexion->lastInsertId();
                // Procesar imágenes si llegaron
                $resultadoUpload = procesar_subida_imagenes($conexion, $productoId);
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'message' => 'Producto creado exitosamente',
                        'imagenes_insertadas' => $resultadoUpload['insertadas'],
                        'errores_imagenes' => $resultadoUpload['errores']
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => 'Error al crear el producto'
                    ]
                ]);
            }
            break;
            
        case 'actualizar':
            $id = $_POST['id'] ?? null;
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $costo = $_POST['costo'] ?? 0;
            $precio_venta = $_POST['precio_venta'] ?? 0;
            $categoria_id = $_POST['categoria_id'] ?? null;
            
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => 'ID de producto no especificado'
                    ]
                ]);
                break;
            }
            
            // Verificar si el producto existe
            $productoExistente = RepositorioProducto::obtener_producto_por_id($conexion, $id);
            if (!$productoExistente) {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => 'Producto no encontrado'
                    ]
                ]);
                break;
            }
            
            // Verificar si el nombre ya existe (excluyendo el producto actual)
            if ($nombre !== $productoExistente->obtener_nombre()) {
                if (RepositorioProducto::nombre_existe($conexion, $nombre)) {
                    echo json_encode([
                        'success' => false,
                        'data' => [
                            'message' => 'Ya existe otro producto con ese nombre'
                        ]
                    ]);
                    break;
                }
            }
            
            $actualizado = RepositorioProducto::actualizar_producto($conexion, $id, $nombre, $descripcion, $costo, $precio_venta, $categoria_id);
            
            if ($actualizado) {
                // Procesar imágenes adicionales si llegaron
                $resultadoUpload = procesar_subida_imagenes($conexion, $id);
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'message' => 'Producto actualizado exitosamente',
                        'imagenes_insertadas' => $resultadoUpload['insertadas'],
                        'errores_imagenes' => $resultadoUpload['errores']
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => 'Error al actualizar el producto'
                    ]
                ]);
            }
            break;
            
        case 'eliminar':
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de producto no especificado'
                ]);
                break;
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
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto eliminado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al eliminar el producto'
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida: ' . $accion
            ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}