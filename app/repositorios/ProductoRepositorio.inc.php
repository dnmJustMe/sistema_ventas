<?php
include_once 'app/entidades/Producto.inc.php';

class RepositorioProducto
{

    public static function obtener_todos($conexion)
    {
        $productos = array();
        if (isset($conexion)) {
            try {
                error_log("🔍 RepositorioProducto::obtener_todos - Iniciando consulta");

                $sql = "SELECT p.*, c.nombre as categoria_nombre 
                        FROM productos p 
                        LEFT JOIN categorias c ON p.categoria_id = c.id 
                        ORDER BY p.nombre";
                $sentencia = $conexion->prepare($sql);
                $sentencia->execute();
                $resultado = $sentencia->fetchAll();

                error_log("📊 RepositorioProducto::obtener_todos - Resultados: " . count($resultado));

                if (count($resultado)) {
                    foreach ($resultado as $fila) {
                        error_log("📦 Producto encontrado: ID=" . $fila['id'] . ", Nombre=" . $fila['nombre']);
                        $producto = new Producto(
                            $fila['id'],
                            $fila['nombre'],
                            $fila['descripcion'],
                            $fila['costo'],
                            $fila['precio_venta'],
                            $fila['categoria_id'],
                            $fila['fecha_creado']
                        );
                        $productos[] = $producto;
                    }
                } else {
                    error_log("❌ RepositorioProducto::obtener_todos - No se encontraron productos");
                }
            } catch (PDOException $ex) {
                $error_msg = "ERROR en obtener_todos: " . $ex->getMessage();
                error_log($error_msg);
                print $error_msg;
            }
        } else {
            error_log("❌ RepositorioProducto::obtener_todos - Conexión no disponible");
        }
        return $productos;
    }

    public static function insertar_producto($conexion, $producto)
    {
        $producto_insertado = false;

        if (isset($conexion)) {
            try {
                $sql = "INSERT INTO productos(nombre, descripcion, costo, precio_venta, categoria_id) 
                        VALUES(:nombre, :descripcion, :costo, :precio_venta, :categoria_id)";

                $sentencia = $conexion->prepare($sql);

                $nombre = $producto->obtener_nombre();
                $descripcion = $producto->obtener_descripcion();
                $costo = $producto->obtener_costo();
                $precio_venta = $producto->obtener_precio_venta();
                $categoria_id = $producto->obtener_categoria_id();

                $sentencia->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $sentencia->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $sentencia->bindParam(':costo', $costo, PDO::PARAM_STR);
                $sentencia->bindParam(':precio_venta', $precio_venta, PDO::PARAM_STR);
                $sentencia->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);

                $producto_insertado = $sentencia->execute();
                error_log("✅ Producto insertado: " . ($producto_insertado ? 'SÍ' : 'NO'));
            } catch (PDOException $ex) {
                error_log('ERROR en insertar_producto: ' . $ex->getMessage());
            }
        }
        return $producto_insertado;
    }

    public static function nombre_existe($conexion, $nombre)
    {
        $nombre_existe = false;

        if (isset($conexion)) {
            try {
                $sql = "SELECT * FROM productos WHERE nombre = :nombre";
                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $sentencia->execute();

                $resultado = $sentencia->fetchAll();

                if (count($resultado)) {
                    $nombre_existe = true;
                }
                error_log("🔍 Nombre existe '" . $nombre . "': " . ($nombre_existe ? 'SÍ' : 'NO'));
            } catch (PDOException $ex) {
                error_log('ERROR en nombre_existe: ' . $ex->getMessage());
            }
        }
        return $nombre_existe;
    }

    public static function obtener_producto_por_id($conexion, $id)
    {
        $producto = null;
        if (isset($conexion)) {
            try {
                error_log("🔍 Buscando producto por ID: " . $id);
                $sql = "SELECT p.*, c.nombre as categoria_nombre 
                        FROM productos p 
                        LEFT JOIN categorias c ON p.categoria_id = c.id 
                        WHERE p.id = :id";
                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':id', $id, PDO::PARAM_INT);
                $sentencia->execute();

                $resultado = $sentencia->fetch();

                if (!empty($resultado)) {
                    error_log("✅ Producto encontrado: " . $resultado['nombre']);
                    $producto = new Producto(
                        $resultado['id'],
                        $resultado['nombre'],
                        $resultado['descripcion'],
                        $resultado['costo'],
                        $resultado['precio_venta'],
                        $resultado['categoria_id'],
                        $resultado['fecha_creado']
                    );
                } else {
                    error_log("❌ Producto no encontrado con ID: " . $id);
                }
            } catch (PDOException $ex) {
                error_log('ERROR en obtener_producto_por_id: ' . $ex->getMessage());
            }
        }
        return $producto;
    }

    public static function actualizar_producto($conexion, $id, $nombre, $descripcion, $costo, $precio_venta, $categoria_id)
    {
        $actualizado = false;
        if (isset($conexion)) {
            try {
                $sql = "UPDATE productos SET nombre = :nombre, descripcion = :descripcion, 
                        costo = :costo, precio_venta = :precio_venta, categoria_id = :categoria_id 
                        WHERE id = :id";

                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $sentencia->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $sentencia->bindParam(':costo', $costo, PDO::PARAM_STR);
                $sentencia->bindParam(':precio_venta', $precio_venta, PDO::PARAM_STR);
                $sentencia->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
                $sentencia->bindParam(':id', $id, PDO::PARAM_INT);

                $actualizado = $sentencia->execute();
                error_log("✅ Producto actualizado ID " . $id . ": " . ($actualizado ? 'SÍ' : 'NO'));
            } catch (PDOException $ex) {
                error_log('ERROR en actualizar_producto: ' . $ex->getMessage());
            }
        }
        return $actualizado;
    }

    public static function eliminar_producto($conexion, $id)
    {
        $eliminado = false;
        if (isset($conexion)) {
            try {
                $sql = "DELETE FROM productos WHERE id = :id";
                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':id', $id, PDO::PARAM_INT);
                $eliminado = $sentencia->execute();
                error_log("🗑️ Producto eliminado ID " . $id . ": " . ($eliminado ? 'SÍ' : 'NO'));
            } catch (PDOException $ex) {
                error_log('ERROR en eliminar_producto: ' . $ex->getMessage());
            }
        }
        return $eliminado;
    }

    public static function obtener_productos_por_categoria($conexion, $categoria_id)
    {
        $productos = array();
        if (isset($conexion)) {
            try {
                error_log("🔍 Obteniendo productos por categoría ID: " . $categoria_id);
                $sql = "SELECT * FROM productos WHERE categoria_id = :categoria_id ORDER BY nombre";
                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
                $sentencia->execute();
                $resultado = $sentencia->fetchAll();

                error_log("📊 Productos por categoría: " . count($resultado));

                if (count($resultado)) {
                    foreach ($resultado as $fila) {
                        $productos[] = new Producto(
                            $fila['id'],
                            $fila['nombre'],
                            $fila['descripcion'],
                            $fila['costo'],
                            $fila['precio_venta'],
                            $fila['categoria_id'],
                            $fila['fecha_creado']
                        );
                    }
                }
            } catch (PDOException $ex) {
                error_log('ERROR en obtener_productos_por_categoria: ' . $ex->getMessage());
            }
        }
        return $productos;
    }

    public static function buscar_producto_nombre($conexion, $busqueda)
    {
        $productos = array();
        $busqueda = '%' . $busqueda . '%';
        if (isset($conexion)) {
            try {
                error_log("🔍 Buscando productos: " . $busqueda);
                $sql = "SELECT p.*, c.nombre as categoria_nombre 
                        FROM productos p 
                        LEFT JOIN categorias c ON p.categoria_id = c.id 
                        WHERE p.nombre LIKE :busqueda OR p.descripcion LIKE :busqueda 
                        ORDER BY p.nombre";
                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':busqueda', $busqueda, PDO::PARAM_STR);
                $sentencia->execute();
                $resultado = $sentencia->fetchAll();

                error_log("📊 Resultados de búsqueda: " . count($resultado));

                if (count($resultado)) {
                    foreach ($resultado as $fila) {
                        $producto = new Producto(
                            $fila['id'],
                            $fila['nombre'],
                            $fila['descripcion'],
                            $fila['costo'],
                            $fila['precio_venta'],
                            $fila['categoria_id'],
                            $fila['fecha_creado']
                        );
                        $productos[] = $producto;
                    }
                }
            } catch (PDOException $ex) {
                error_log("ERROR en buscar_producto_nombre: " . $ex->getMessage());
            }
        }
        return $productos;
    }
}
