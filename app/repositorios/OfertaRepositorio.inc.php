<?php
include_once 'app/entidades/Oferta.inc.php';
include_once 'app/entidades/Producto.inc.php';
include_once 'app/repositorios/ImagenRepositorio.inc.php';

class RepositorioOferta
{
    public static function obtener_todos($conexion)
    {
        $ofertas = array();
        if (isset($conexion)) {
            try {
                $sql = "SELECT * FROM ofertas ORDER BY fecha_creado DESC";
                $sentencia = $conexion->prepare($sql);
                $sentencia->execute();
                $resultado = $sentencia->fetchAll();

                if (count($resultado)) {
                    foreach ($resultado as $fila) {
                        $ofertas[] = new Oferta(
                            $fila['id'],
                            $fila['nombre'],
                            $fila['descripcion'],
                            $fila['precio_final_venta'],
                            $fila['fecha_creado']
                        );
                    }
                }
            } catch (PDOException $ex) {
                error_log('ERROR en obtener_todos ofertas: ' . $ex->getMessage());
            }
        }
        return $ofertas;
    }

    public static function insertar_oferta($conexion, $oferta)
    {
        $oferta_insertada = false;

        if (isset($conexion)) {
            try {
                $sql = "INSERT INTO ofertas(nombre, descripcion, precio_final_venta) 
                        VALUES(:nombre, :descripcion, :precio_final_venta)";

                $sentencia = $conexion->prepare($sql);

                $nombre = $oferta->obtener_nombre();
                $descripcion = $oferta->obtener_descripcion();
                $precio_final_venta = $oferta->obtener_precio_final_venta();

                $sentencia->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $sentencia->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $sentencia->bindParam(':precio_final_venta', $precio_final_venta, PDO::PARAM_STR);

                $oferta_insertada = $sentencia->execute();
                error_log("✅ Oferta insertada: " . ($oferta_insertada ? 'SÍ' : 'NO'));
            } catch (PDOException $ex) {
                error_log('ERROR en insertar_oferta: ' . $ex->getMessage());
            }
        }
        return $oferta_insertada;
    }

    public static function nombre_existe($conexion, $nombre)
    {
        $nombre_existe = false;

        if (isset($conexion)) {
            try {
                $sql = "SELECT * FROM ofertas WHERE nombre = :nombre";
                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $sentencia->execute();

                $resultado = $sentencia->fetchAll();

                if (count($resultado)) {
                    $nombre_existe = true;
                }
            } catch (PDOException $ex) {
                error_log('ERROR en nombre_existe ofertas: ' . $ex->getMessage());
            }
        }
        return $nombre_existe;
    }

    public static function obtener_oferta_por_id($conexion, $id)
    {
        $oferta = null;
        if (isset($conexion)) {
            try {
                $sql = "SELECT * FROM ofertas WHERE id = :id";
                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':id', $id, PDO::PARAM_INT);
                $sentencia->execute();

                $resultado = $sentencia->fetch();

                if (!empty($resultado)) {
                    $oferta = new Oferta(
                        $resultado['id'],
                        $resultado['nombre'],
                        $resultado['descripcion'],
                        $resultado['precio_final_venta'],
                        $resultado['fecha_creado']
                    );
                }
            } catch (PDOException $ex) {
                error_log('ERROR en obtener_oferta_por_id: ' . $ex->getMessage());
            }
        }
        return $oferta;
    }

    public static function obtener_productos_por_oferta($conexion, $oferta_id)
    {
        $productos_con_cantidad = array();
        if (isset($conexion)) {
            try {
                $sql = "SELECT p.*, op.cantidad 
                        FROM productos p 
                        JOIN oferta_productos op ON p.id = op.producto_id 
                        WHERE op.oferta_id = :oferta_id 
                        ORDER BY p.nombre";
                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':oferta_id', $oferta_id, PDO::PARAM_INT);
                $sentencia->execute();
                $resultado = $sentencia->fetchAll();

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
                        // Crear array con producto y cantidad
                        $productos_con_cantidad[] = [
                            'producto' => $producto,
                            'cantidad' => $fila['cantidad']
                        ];
                    }
                }
            } catch (PDOException $ex) {
                error_log('ERROR en obtener_productos_por_oferta: ' . $ex->getMessage());
            }
        }
        return $productos_con_cantidad;
    }

    public static function agregar_producto_a_oferta($conexion, $oferta_id, $producto_id, $cantidad = 1)
    {
        $agregado = false;
        if (isset($conexion)) {
            try {
                $sql = "INSERT INTO oferta_productos(oferta_id, producto_id, cantidad) 
                        VALUES(:oferta_id, :producto_id, :cantidad)";
                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':oferta_id', $oferta_id, PDO::PARAM_INT);
                $sentencia->bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
                $sentencia->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                $agregado = $sentencia->execute();
            } catch (PDOException $ex) {
                error_log('ERROR en agregar_producto_a_oferta: ' . $ex->getMessage());
            }
        }
        return $agregado;
    }

    // NUEVO MÉTODO: Eliminar todos los productos de una oferta
    public static function eliminar_productos_oferta($conexion, $oferta_id)
    {
        $eliminado = false;
        if (isset($conexion)) {
            try {
                $sql = "DELETE FROM oferta_productos WHERE oferta_id = :oferta_id";
                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':oferta_id', $oferta_id, PDO::PARAM_INT);
                $eliminado = $sentencia->execute();
                error_log("🗑️ Productos eliminados de oferta ID " . $oferta_id . ": " . ($eliminado ? 'SÍ' : 'NO'));
            } catch (PDOException $ex) {
                error_log('ERROR en eliminar_productos_oferta: ' . $ex->getMessage());
            }
        }
        return $eliminado;
    }

    public static function actualizar_oferta($conexion, $id, $nombre, $descripcion, $precio_final_venta)
    {
        $actualizado = false;
        if (isset($conexion)) {
            try {
                $sql = "UPDATE ofertas SET nombre = :nombre, descripcion = :descripcion, 
                        precio_final_venta = :precio_final_venta WHERE id = :id";

                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $sentencia->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $sentencia->bindParam(':precio_final_venta', $precio_final_venta, PDO::PARAM_STR);
                $sentencia->bindParam(':id', $id, PDO::PARAM_INT);

                $actualizado = $sentencia->execute();
                error_log("✅ Oferta actualizada ID " . $id . ": " . ($actualizado ? 'SÍ' : 'NO'));
            } catch (PDOException $ex) {
                error_log('ERROR en actualizar_oferta: ' . $ex->getMessage());
            }
        }
        return $actualizado;
    }

    public static function eliminar_oferta($conexion, $id)
    {
        $eliminado = false;
        if (isset($conexion)) {
            try {
                $sql = "DELETE FROM ofertas WHERE id = :id";
                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':id', $id, PDO::PARAM_INT);
                $eliminado = $sentencia->execute();
                error_log("🗑️ Oferta eliminada ID " . $id . ": " . ($eliminado ? 'SÍ' : 'NO'));
            } catch (PDOException $ex) {
                error_log('ERROR en eliminar_oferta: ' . $ex->getMessage());
            }
        }
        return $eliminado;
    }

    public static function buscar_oferta_nombre($conexion, $busqueda)
    {
        $ofertas = array();
        $busqueda = '%' . $busqueda . '%';
        if (isset($conexion)) {
            try {
                $sql = "SELECT * FROM ofertas WHERE nombre LIKE :busqueda OR descripcion LIKE :busqueda ORDER BY nombre";
                $sentencia = $conexion->prepare($sql);
                $sentencia->bindParam(':busqueda', $busqueda, PDO::PARAM_STR);
                $sentencia->execute();
                $resultado = $sentencia->fetchAll();

                if (count($resultado)) {
                    foreach ($resultado as $fila) {
                        $ofertas[] = new Oferta(
                            $fila['id'],
                            $fila['nombre'],
                            $fila['descripcion'],
                            $fila['precio_final_venta'],
                            $fila['fecha_creado']
                        );
                    }
                }
            } catch (PDOException $ex) {
                error_log("ERROR en buscar_oferta_nombre: " . $ex->getMessage());
            }
        }
        return $ofertas;
    }

    public static function obtener_imagen_principal_oferta($conexion, $oferta_id)
    {
        return RepositorioImagen::obtener_imagen_principal_por_entidad($conexion, 'oferta', $oferta_id);
    }

    public static function agregar_imagen_a_oferta($conexion, $oferta_id, $path, $es_principal = 1)
    {
        $imagen = new Imagen(null, null, $oferta_id, null, $path, null, $es_principal);
        return RepositorioImagen::insertar_imagen($conexion, $imagen);
    }

    public static function eliminar_imagenes_por_oferta($conexion, $oferta_id)
    {
        return RepositorioImagen::eliminar_imagenes_por_oferta($conexion, $oferta_id);
    }
}
?>