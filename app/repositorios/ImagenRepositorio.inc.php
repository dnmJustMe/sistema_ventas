<?php
include_once 'app/entidades/Imagen.inc.php';

class RepositorioImagen{
    
   public static function obtener_todos($conexion){
       $imagenes = array(); 
       if(isset($conexion)){
           try{
               $sql = "SELECT i.*, p.nombre as producto_nombre 
                       FROM imagenes i 
                       JOIN productos p ON i.producto_id = p.id 
                       ORDER BY i.fecha_creado DESC";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                      $imagen = new Imagen($fila['id'], $fila['producto_id'], $fila['path'], $fila['fecha_creado'], $fila['es_principal'] ?? 0);
                    //    $imagen->producto_nombre = $fila['producto_nombre'];d
                       $imagenes[] = $imagen;
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $imagenes;
   } 
    
    public static function obtener_numero_imagenes($conexion){
       $total_imagenes = null;
        if(isset($conexion)){
            try{
                $sql = "SELECT COUNT(*) as total FROM imagenes";
                $sentencia = $conexion -> prepare($sql);
                $sentencia ->execute();
                $resultado = $sentencia -> fetch();
                $total_imagenes = $resultado['total'];
            }catch(PDOException $ex){
                print 'ERROR' . $ex ->getMessage();
            }
        }
        return $total_imagenes;
    }
	
	public static function insertar_imagen($conexion, $imagen){
		$imagen_insertada = false;
		
		if(isset($conexion)){
			try{
                $sql = "INSERT INTO imagenes(producto_id, path, es_principal) VALUES(:producto_id, :path, :es_principal)";
				
                $sentencia = $conexion -> prepare($sql);
                
                $producto_id = $imagen -> obtener_producto_id();
                $path = $imagen -> obtener_path();
                $es_principal = $imagen -> obtener_es_principal();
				
				$sentencia -> bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
                $sentencia -> bindParam(':path', $path, PDO::PARAM_STR);
                $sentencia -> bindParam(':es_principal', $es_principal, PDO::PARAM_INT);
				
				$imagen_insertada = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $imagen_insertada;
	}
	
	public static function obtener_imagen_por_id($conexion, $id){
		$imagen = null;
		if(isset($conexion)){
			try{
				$sql = "SELECT i.*, p.nombre as producto_nombre 
						FROM imagenes i 
						JOIN productos p ON i.producto_id = p.id 
						WHERE i.id = :id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				$sentencia -> execute();
				
				$resultado = $sentencia -> fetch();
				
				if(!empty($resultado)){
					$imagen = new Imagen($resultado['id'], $resultado['producto_id'], $resultado['path'], $resultado['fecha_creado']);
					// $imagen->producto_nombre = $resultado['producto_nombre'];
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $imagen;
	}

	public static function obtener_imagenes_por_producto($conexion, $producto_id){
		$imagenes = array();
		if(isset($conexion)){
			try{
                $sql = "SELECT * FROM imagenes WHERE producto_id = :producto_id ORDER BY es_principal DESC, fecha_creado";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
				$sentencia -> execute();
				$resultado = $sentencia -> fetchAll();
				
				if(count($resultado)){
					foreach($resultado as $fila){
                        $imagenes[] = new Imagen($fila['id'], $fila['producto_id'], $fila['path'], $fila['fecha_creado'], $fila['es_principal'] ?? 0);
					}
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $imagenes;
	}

	public static function eliminar_imagen($conexion, $id){
		$eliminado = false;
		if(isset($conexion)){
			try{
				$sql = "DELETE FROM imagenes WHERE id = :id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				$eliminado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $eliminado;
	}

	public static function eliminar_imagenes_por_producto($conexion, $producto_id){
		$eliminado = false;
		if(isset($conexion)){
			try{
				$sql = "DELETE FROM imagenes WHERE producto_id = :producto_id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
				$eliminado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $eliminado;
	}
}
?>