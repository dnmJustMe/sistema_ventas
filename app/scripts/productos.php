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

header('Content-Type: application/json');

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
                                'fecha_creado' => $producto->obtener_fecha_creado()
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
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'message' => 'Producto creado exitosamente'
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
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'message' => 'Producto actualizado exitosamente'
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
            
            $eliminado = RepositorioProducto::eliminar_producto($conexion, $id);
            
            if ($eliminado) {
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