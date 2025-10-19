<?php
// app/scripts/servicios.php

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
include_once 'app/repositorios/ServicioRepositorio.inc.php';
include_once 'app/repositorios/ImagenRepositorio.inc.php';
include_once 'app/entidades/Servicio.inc.php';
include_once 'app/entidades/Imagen.inc.php';

header('Content-Type: application/json');

// Configuración de subida de imágenes
define('IMG_MAX_BYTES', 5 * 1024 * 1024); // 5MB
define('IMG_ALLOWED_MIME', ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif']);

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

function procesar_subida_imagenes($conexion, $tipoEntidad, $entidadId) {
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

        $filename = generar_nombre_archivo($tipoEntidad, $entidadId, $ext);
        $destino = $uploadDir . $filename;

        if (!@move_uploaded_file($tmpNames[$i], $destino)) {
            $errores[] = 'No se pudo guardar el archivo: ' . $names[$i];
            continue;
        }

        // Guardar en BD solo el nombre del archivo
        $esPrincipal = 0;
        
        // Crear imagen según el tipo de entidad
        $imagen = new Imagen(
            null, 
            null, // producto_id
            null, // oferta_id
            $entidadId, // servicio_id
            $filename, 
            null, 
            $esPrincipal
        );
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
    
    // Primero quitar principal de todas las imágenes de este servicio
    $sqlQuitarPrincipal = "UPDATE imagenes SET es_principal = 0 WHERE $campoId = :eid";
    $stmtQuitar = $conexion->prepare($sqlQuitarPrincipal);
    $stmtQuitar->bindParam(':eid', $entidadId, PDO::PARAM_INT);
    $stmtQuitar->execute();
        
    // Crear imagen según el tipo de entidad
    $imagen = new Imagen(
        null, 
        null, // producto_id
        null, // oferta_id
        $entidadId, // servicio_id
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

function contar_imagenes_entidad($conexion, $tipoEntidad, $entidadId) {
    $campoId = $tipoEntidad . '_id';
    $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM imagenes WHERE $campoId = :eid");
    $stmt->bindParam(':eid', $entidadId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
}

function unificar_principal($conexion, $tipoEntidad, $entidadId, $preferId = null) {
    $campoId = $tipoEntidad . '_id';
    
    if ($preferId) {
        // Forzar la imagen indicada como principal
        $conexion->prepare("UPDATE imagenes SET es_principal = 0 WHERE $campoId = :eid")
            ->execute([':eid' => $entidadId]);
        $stmt = $conexion->prepare("UPDATE imagenes SET es_principal = 1 WHERE id = :id AND $campoId = :eid");
        $stmt->bindParam(':id', $preferId, PDO::PARAM_INT);
        $stmt->bindParam(':eid', $entidadId, PDO::PARAM_INT);
        $stmt->execute();
        return;
    }

    // Si ya hay imágenes marcadas como principal, dejar solo una y limpiar el resto
    $stmt = $conexion->prepare("SELECT id FROM imagenes WHERE $campoId = :eid AND es_principal = 1 ORDER BY id DESC");
    $stmt->bindParam(':eid', $entidadId, PDO::PARAM_INT);
    $stmt->execute();
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    if (!empty($ids)) {
        $keepId = (int)$ids[0];
        if (count($ids) > 1) {
            // Poner en 0 todas excepto la elegida
            $in = implode(',', array_map('intval', array_slice($ids, 1)));
            if ($in !== '') {
                $conexion->exec("UPDATE imagenes SET es_principal = 0 WHERE $campoId = " . (int)$entidadId . " AND id IN (" . $in . ")");
            }
        }
        return;
    }

    // Si ninguna está marcada, establecer la más reciente como principal
    $stmt = $conexion->prepare("SELECT id FROM imagenes WHERE $campoId = :eid ORDER BY id DESC LIMIT 1");
    $stmt->bindParam(':eid', $entidadId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    if (!empty($row)) {
        $id = (int)$row['id'];
        $stmt = $conexion->prepare("UPDATE imagenes SET es_principal = 1 WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
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
                $servicios = RepositorioServicio::buscar_servicio_nombre($conexion, $busqueda);
            } else {
                $servicios = RepositorioServicio::obtener_todos($conexion);
            }
            
            // Obtener imágenes para cada servicio
            $serviciosConImagenes = [];
            foreach ($servicios as $servicio) {
                $imagenes = RepositorioImagen::obtener_imagenes_por_servicio($conexion, $servicio->obtener_id());
                $imagenesArray = [];
                if (!empty($imagenes)) {
                    foreach ($imagenes as $img) {
                        $imagenesArray[] = [
                            'id' => $img->obtener_id(),
                            'path' => $img->obtener_path(),
                            'es_principal' => (bool)$img->obtener_es_principal(),
                        ];
                    }
                }
                $servicioArray = [
                    'id' => $servicio->obtener_id(),
                    'nombre' => $servicio->obtener_nombre(),
                    'descripcion' => $servicio->obtener_descripcion(),
                    'fecha_creado' => $servicio->obtener_fecha_creado(),
                    'imagenes' => $imagenesArray
                ];
                $serviciosConImagenes[] = $servicioArray;
            }
            
            send_json([
                'success' => true,
                'data' => [
                    'servicios' => $serviciosConImagenes
                ]
            ]);
            break;
            
        case 'obtener':
            $id = $_POST['id'] ?? null;
            if ($id) {
                $servicio = RepositorioServicio::obtener_servicio_por_id($conexion, $id);
                if ($servicio) {
                    // Obtener imágenes del servicio
                    $imgs = RepositorioImagen::obtener_imagenes_por_servicio($conexion, $id);
                    $imagenes = [];
                    if (!empty($imgs)) {
                        foreach ($imgs as $img) {
                            $imagenes[] = [
                                'id' => $img->obtener_id(),
                                'path' => $img->obtener_path(),
                                'es_principal' => (bool)$img->obtener_es_principal(),
                            ];
                        }
                    }
                    send_json([
                        'success' => true,
                        'data' => [
                            'servicio' => [
                                'id' => $servicio->obtener_id(),
                                'nombre' => $servicio->obtener_nombre(),
                                'descripcion' => $servicio->obtener_descripcion(),
                                'fecha_creado' => $servicio->obtener_fecha_creado(),
                                'imagenes' => $imagenes,
                            ]
                        ]
                    ]);
                } else {
                    send_json([
                        'success' => false,
                        'data' => [
                            'message' => 'Servicio no encontrado'
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
            break;
            
        case 'crear':
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            // Validaciones
            if (empty($nombre)) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'El nombre es requerido'
                    ]
                ], 400);
            }
            
            if (RepositorioServicio::nombre_existe($conexion, $nombre)) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Ya existe un servicio con ese nombre'
                    ]
                ], 409);
            }
            
            $servicio = new Servicio(null, $nombre, $descripcion, null);
            $insertado = RepositorioServicio::insertar_servicio($conexion, $servicio);
            
            if ($insertado) {
                // ID del servicio insertado
                $servicioId = $conexion->lastInsertId();
                
                // Procesar imagen principal (obligatoria en creación)
                $principalResult = procesar_subida_imagen_principal($conexion, 'servicio', $servicioId);
                
                // Procesar imágenes adicionales si llegaron
                $resultadoUpload = procesar_subida_imagenes($conexion, 'servicio', $servicioId);
                
                // Validar que haya al menos 1 imagen y una principal
                $total = contar_imagenes_entidad($conexion, 'servicio', $servicioId);
                if ($total === 0) {
                    // Si no hay imágenes, eliminar el servicio creado
                    RepositorioServicio::eliminar_servicio($conexion, $servicioId);
                    send_json([
                        'success' => false,
                        'data' => [ 'message' => 'Debe subir al menos una imagen del servicio' ]
                    ], 400);
                }
                
                send_json([
                    'success' => true,
                    'data' => [
                        'message' => 'Servicio creado exitosamente',
                        'imagenes_insertadas' => $resultadoUpload['insertadas'] + $principalResult['insertadas'],
                        'errores_imagenes' => array_merge($resultadoUpload['errores'], $principalResult['errores'])
                    ]
                ]);
            } else {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Error al crear el servicio'
                    ]
                ], 500);
            }
            break;
            
        case 'actualizar':
            $id = $_POST['id'] ?? null;
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            if (!$id) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'ID de servicio no especificado'
                    ]
                ], 400);
            }
            
            // Verificar si el servicio existe
            $servicioExistente = RepositorioServicio::obtener_servicio_por_id($conexion, $id);
            if (!$servicioExistente) {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Servicio no encontrado'
                    ]
                ], 404);
            }
            
            // Verificar si el nombre ya existe (excluyendo el servicio actual)
            if ($nombre !== $servicioExistente->obtener_nombre()) {
                if (RepositorioServicio::nombre_existe($conexion, $nombre)) {
                    send_json([
                        'success' => false,
                        'data' => [
                            'message' => 'Ya existe otro servicio con ese nombre'
                        ]
                    ], 409);
                }
            }
            
            $actualizado = RepositorioServicio::actualizar_servicio($conexion, $id, $nombre, $descripcion);
            
            if ($actualizado) {
                // Si subieron imagen principal nueva, procesarla (reemplaza principal)
                $principalResult = procesar_subida_imagen_principal($conexion, 'servicio', $id);

                // Eliminar imágenes solicitadas (si hay)
                if (!empty($_POST['imagenes_eliminar'])) {
                    $idsEliminar = array_filter(array_map('intval', explode(',', $_POST['imagenes_eliminar'])));
                    if (!empty($idsEliminar)) {
                        $uploadDir = obtener_directorio_upload();
                        $existentes = RepositorioImagen::obtener_imagenes_por_servicio($conexion, $id);
                        $mapa = [];
                        foreach ($existentes as $img) { 
                            $mapa[$img->obtener_id()] = $img; 
                        }
                        foreach ($idsEliminar as $imgId) {
                            if (isset($mapa[$imgId])) {
                                $file = $uploadDir . $mapa[$imgId]->obtener_path();
                                if (is_file($file)) { 
                                    @unlink($file); 
                                }
                                RepositorioImagen::eliminar_imagen($conexion, $imgId);
                            }
                        }
                    }
                }

                // Subir nuevas imágenes si llegaron
                $resultadoUpload = procesar_subida_imagenes($conexion, 'servicio', $id);

                // Determinar y fijar principal
                $preferId = null;
                if (!empty($_POST['imagen_principal_existente']) && ($principalResult['insertadas'] ?? 0) === 0) {
                    $preferId = (int)$_POST['imagen_principal_existente'];
                }
                
                if ($preferId) {
                    unificar_principal($conexion, 'servicio', $id, $preferId);
                }

                send_json([
                    'success' => true,
                    'data' => [
                        'message' => 'Servicio actualizado exitosamente',
                        'imagenes_insertadas' => $resultadoUpload['insertadas'] + ($principalResult['insertadas'] ?? 0),
                        'errores_imagenes' => array_merge($resultadoUpload['errores'] ?? [], $principalResult['errores'] ?? [])
                    ]
                ]);
            } else {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Error al actualizar el servicio'
                    ]
                ], 500);
            }
            break;
            
        case 'eliminar':
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                send_json([
                    'success' => false,
                    'message' => 'ID de servicio no especificado'
                ], 400);
            }
            
            // Eliminar imágenes asociadas al servicio
            eliminar_imagenes_entidad($conexion, 'servicio', $id);
            
            // Eliminar servicio
            $eliminado = RepositorioServicio::eliminar_servicio($conexion, $id);
            
            if ($eliminado) {
                send_json([
                    'success' => true,
                    'data' => [
                        'message' => 'Servicio eliminado exitosamente'
                    ]
                ]);
            } else {
                send_json([
                    'success' => false,
                    'data' => [
                        'message' => 'Error al eliminar el servicio'
                    ]
                ], 500);
            }
            break;
            
        default:
            send_json([
                'success' => false,
                'data' => [
                    'message' => 'Acción no válida: ' . $accion
                ]
            ], 400);
    }
    
} catch (Exception $e) {
    error_log("Error en servicios.php: " . $e->getMessage());
    send_json([
        'success' => false,
        'data' => [
            'message' => 'Error del servidor: ' . $e->getMessage()
        ]
    ], 500);
}