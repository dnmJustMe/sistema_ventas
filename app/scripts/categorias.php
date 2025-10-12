<?php
// app/scripts/categorias.php

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
include_once 'app/repositorios/CategoriaRepositorio.inc.php';
include_once 'app/repositorios/ProductoRepositorio.inc.php';

header('Content-Type: application/json');

try {
    $conexion = Conexion::obtener_conexion();

    // Leer datos de POST (ya que todas las peticiones son POST)
    $accion = $_POST['accion'] ?? 'listar';

    switch ($accion) {
        case 'listar':
            $busqueda = $_POST['busqueda'] ?? '';

            if (!empty($busqueda)) {
                $categorias = RepositorioCategoria::buscar_categoria_nombre($conexion, $busqueda);
            } else {
                $categorias = RepositorioCategoria::obtener_todos($conexion);
            }

            // Obtener número de productos por categoría
            $categoriasConConteo = [];
            foreach ($categorias as $categoria) {
                $productos = RepositorioProducto::obtener_productos_por_categoria($conexion, $categoria->obtener_id());
                $categoriaArray = [
                    'id' => $categoria->obtener_id(),
                    'nombre' => $categoria->obtener_nombre(),
                    'fecha_creado' => $categoria->obtener_fecha_creado(),
                    'total_productos' => count($productos)
                ];
                $categoriasConConteo[] = $categoriaArray;
            }

            echo json_encode([
                'success' => true,
                'categorias' => $categoriasConConteo
            ]);
            break;

        case 'obtener':
            $id = $_POST['id'] ?? null;
            if ($id) {
                $categoria = RepositorioCategoria::obtener_categoria_por_id($conexion, $id);
                if ($categoria) {
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'categoria' => [
                                'id' => $categoria->obtener_id(),
                                'nombre' => $categoria->obtener_nombre(),
                                'fecha_creado' => $categoria->obtener_fecha_creado()
                            ]
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'data' => [
                            'message' => 'Categoría no encontrada'
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

            if (RepositorioCategoria::nombre_existe($conexion, $nombre)) {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => 'Ya existe una categoría con ese nombre'
                    ]
                ]);
                break;
            }

            $categoria = new Categoria(null, $nombre, null);
            $insertada = RepositorioCategoria::insertar_categoria($conexion, $categoria);

            if ($insertada) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'message' => 'Categoría creada exitosamente'
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => 'Error al crear la categoría'
                    ]
                ]);
            }
            break;

        case 'actualizar':
            $id = $_POST['id'] ?? null;
            $nombre = $_POST['nombre'] ?? '';

            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => 'ID de categoría no especificado'
                    ]
                ]);
                break;
            }

            // Verificar si la categoría existe
            $categoriaExistente = RepositorioCategoria::obtener_categoria_por_id($conexion, $id);
            if (!$categoriaExistente) {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => 'Categoría no encontrada'
                    ]
                ]);
                break;
            }

            // Verificar si el nombre ya existe (excluyendo la categoría actual)
            if ($nombre !== $categoriaExistente->obtener_nombre()) {
                if (RepositorioCategoria::nombre_existe($conexion, $nombre)) {
                    echo json_encode([
                        'success' => false,
                        'data' => [
                            'message' => 'Ya existe otra categoría con ese nombre'
                        ]
                    ]);
                    break;
                }
            }

            $actualizada = RepositorioCategoria::actualizar_categoria($conexion, $id, $nombre);

            if ($actualizada) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'message' => 'Categoría actualizada exitosamente'
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => 'Error al actualizar la categoría'
                    ]
                ]);
            }
            break;

        case 'eliminar':
            $id = $_POST['id'] ?? null;

            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de categoría no especificado'
                ]);
                break;
            }

            // Verificar si la categoría existe
            $categoriaExistente = RepositorioCategoria::obtener_categoria_por_id($conexion, $id);
            if (!$categoriaExistente) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ]);
                break;
            }

            // Verificar si la categoría tiene productos
            $productos = RepositorioProducto::obtener_productos_por_categoria($conexion, $id);
            if (count($productos) > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se puede eliminar la categoría porque tiene productos asociados'
                ]);
                break;
            }

            $eliminada = RepositorioCategoria::eliminar_categoria($conexion, $id);

            if ($eliminada) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría eliminada exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al eliminar la categoría'
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