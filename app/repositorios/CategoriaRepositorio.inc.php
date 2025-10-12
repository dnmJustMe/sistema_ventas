<?php
include_once 'app/entidades/Categoria.inc.php';

class RepositorioCategoria {
    
    public static function obtener_todos($conexion) {
        $categorias = array(); 
        if(isset($conexion)) {
            try {
                error_log("🔍 RepositorioCategoria::obtener_todos - Iniciando consulta");
                
                $sql = "SELECT * FROM categorias ORDER BY nombre";
                $sentencia = $conexion -> prepare($sql);
                $sentencia -> execute();
                $resultado = $sentencia -> fetchAll();
                
                error_log("📊 RepositorioCategoria::obtener_todos - Resultados: " . count($resultado));
                
                if(count($resultado)) {
                    foreach($resultado as $fila) {
                        error_log("🏷️ Categoría encontrada: ID=" . $fila['id'] . ", Nombre=" . $fila['nombre']);
                        $categorias[] = new Categoria($fila['id'], $fila['nombre'], $fila['fecha_creado']);
                    }
                } else {
                    error_log("❌ RepositorioCategoria::obtener_todos - No se encontraron categorías");
                }
                
            } catch (PDOException $ex) {
                error_log("ERROR en obtener_todos: " . $ex -> getMessage());
            }
        } else {
            error_log("❌ RepositorioCategoria::obtener_todos - Conexión no disponible");
        }
        return $categorias;
    } 
    
    public static function obtener_numero_categorias($conexion) {
        $total_categorias = null;
        if(isset($conexion)) {
            try {
                $sql = "SELECT COUNT(*) as total FROM categorias";
                $sentencia = $conexion -> prepare($sql);
                $sentencia -> execute();
                $resultado = $sentencia -> fetch();
                $total_categorias = $resultado['total'];
                error_log("📊 Total de categorías: " . $total_categorias);
            } catch(PDOException $ex) {
                error_log('ERROR en obtener_numero_categorias: ' . $ex -> getMessage());
            }
        }
        return $total_categorias;
    }
    
    public static function insertar_categoria($conexion, $categoria) {
        $categoria_insertada = false;
        
        if(isset($conexion)) {
            try {
                $sql = "INSERT INTO categorias(nombre) VALUES(:nombre)";
                
                $sentencia = $conexion -> prepare($sql);
                $nombre = $categoria -> obtener_nombre();
                
                $sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $categoria_insertada = $sentencia -> execute();
                error_log("✅ Categoría insertada: " . ($categoria_insertada ? 'SÍ' : 'NO') . " - Nombre: " . $nombre);
            } catch(PDOException $ex) {
                error_log('ERROR en insertar_categoria: ' . $ex->getMessage());
            }
        }
        return $categoria_insertada;
    }
    
    public static function nombre_existe($conexion, $nombre) {
        $nombre_existe = false;
        
        if(isset($conexion)) {
            try {
                $sql = "SELECT * FROM categorias WHERE nombre = :nombre";
                $sentencia = $conexion -> prepare($sql);
                $sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $sentencia -> execute();
                
                $resultado = $sentencia -> fetchAll();
                
                if(count($resultado)) {
                    $nombre_existe = true;
                }
                error_log("🔍 Nombre de categoría existe '" . $nombre . "': " . ($nombre_existe ? 'SÍ' : 'NO'));
            } catch(PDOException $ex) {
                error_log('ERROR en nombre_existe: ' . $ex -> getMessage());
            }
        }
        return $nombre_existe;
    } 
    
    public static function obtener_categoria_por_id($conexion, $id) {
        $categoria = null;
        if(isset($conexion)) {
            try {
                error_log("🔍 Buscando categoría por ID: " . $id);
                $sql = "SELECT * FROM categorias WHERE id = :id";
                $sentencia = $conexion -> prepare($sql);
                $sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
                $sentencia -> execute();
                
                $resultado = $sentencia -> fetch();
                
                if(!empty($resultado)) {
                    error_log("✅ Categoría encontrada: " . $resultado['nombre']);
                    $categoria = new Categoria($resultado['id'], $resultado['nombre'], $resultado['fecha_creado']);
                } else {
                    error_log("❌ Categoría no encontrada con ID: " . $id);
                }
            } catch(PDOException $ex) {
                error_log('ERROR en obtener_categoria_por_id: ' . $ex -> getMessage());
            }
        }
        return $categoria;
    }
    
    public static function buscar_categoria_nombre($conexion, $busqueda) {
        $categorias = array(); 
        $busqueda = '%'. $busqueda . '%';
        if(isset($conexion)) {
            try {
                error_log("🔍 Buscando categorías: " . $busqueda);
                $sql = "SELECT * FROM categorias WHERE nombre LIKE :busqueda ORDER BY nombre";
                $sentencia = $conexion -> prepare($sql);
                $sentencia -> bindParam(':busqueda', $busqueda, PDO::PARAM_STR);
                $sentencia -> execute();
                $resultado = $sentencia -> fetchAll();
                
                error_log("📊 Resultados de búsqueda categorías: " . count($resultado));
                
                if(count($resultado)) {
                    foreach($resultado as $fila) {
                        $categorias[] = new Categoria($fila['id'], $fila['nombre'], $fila['fecha_creado']);
                    }
                }
                
            } catch (PDOException $ex) {
                error_log("ERROR en buscar_categoria_nombre: " . $ex -> getMessage());
            }
        }
        return $categorias;
    }

    public static function actualizar_categoria($conexion, $id, $nombre) {
        $actualizado = false;
        if(isset($conexion)) {
            try {
                $sql = "UPDATE categorias SET nombre = :nombre WHERE id = :id";
                $sentencia = $conexion -> prepare($sql);
                $sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
                $actualizado = $sentencia -> execute();
                error_log("✅ Categoría actualizada ID " . $id . ": " . ($actualizado ? 'SÍ' : 'NO'));
            } catch(PDOException $ex) {
                error_log('ERROR en actualizar_categoria: ' . $ex->getMessage());
            }
        }
        return $actualizado;
    }

    public static function eliminar_categoria($conexion, $id) {
        $eliminado = false;
        if(isset($conexion)) {
            try {
                $sql = "DELETE FROM categorias WHERE id = :id";
                $sentencia = $conexion -> prepare($sql);
                $sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
                $eliminado = $sentencia -> execute();
                error_log("🗑️ Categoría eliminada ID " . $id . ": " . ($eliminado ? 'SÍ' : 'NO'));
            } catch(PDOException $ex) {
                error_log('ERROR en eliminar_categoria: ' . $ex->getMessage());
            }
        }
        return $eliminado;
    }

    public static function obtener_categorias_con_productos($conexion) {
        $categorias = array();
        if(isset($conexion)) {
            try {
                $sql = "SELECT c.*, COUNT(p.id) as total_productos 
                        FROM categorias c 
                        LEFT JOIN productos p ON c.id = p.categoria_id 
                        GROUP BY c.id 
                        ORDER BY c.nombre";
                $sentencia = $conexion -> prepare($sql);
                $sentencia -> execute();
                $resultado = $sentencia -> fetchAll();
                
                if(count($resultado)) {
                    foreach($resultado as $fila) {
                        $categoria = new Categoria($fila['id'], $fila['nombre'], $fila['fecha_creado']);
                        $categorias[] = $categoria;
                    }
                }
            } catch(PDOException $ex) {
                error_log('ERROR en obtener_categorias_con_productos: ' . $ex -> getMessage());
            }
        }
        return $categorias;
    }
}
?>