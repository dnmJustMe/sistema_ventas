<?php
include_once 'app/entidades/Imagen.inc.php';

class RepositorioImagen{
    
   public static function obtener_todos($conexion){
       $imagenes = array(); 
       if(isset($conexion)){
           try{
               $sql = "SELECT i.*, 
                              p.nombre as producto_nombre,
                              o.nombre as oferta_nombre,
                              s.nombre as servicio_nombre
                       FROM imagenes i 
                       LEFT JOIN productos p ON i.producto_id = p.id 
                       LEFT JOIN ofertas o ON i.oferta_id = o.id
                       LEFT JOIN servicios s ON i.servicio_id = s.id
                       ORDER BY i.fecha_creado DESC";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                      $imagen = new Imagen(
                          $fila['id'], 
                          $fila['producto_id'], 
                          $fila['oferta_id'], 
                          $fila['servicio_id'], 
                          $fila['path'], 
                          $fila['fecha_creado'], 
                          $fila['es_principal'] ?? 0
                      );
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
                $sql = "INSERT INTO imagenes(producto_id, oferta_id, servicio_id, path, es_principal) 
                        VALUES(:producto_id, :oferta_id, :servicio_id, :path, :es_principal)";
				
                $sentencia = $conexion -> prepare($sql);
                
                $producto_id = $imagen -> obtener_producto_id();
                $oferta_id = $imagen -> obtener_oferta_id();
                $servicio_id = $imagen -> obtener_servicio_id();
                $path = $imagen -> obtener_path();
                $es_principal = $imagen -> obtener_es_principal();
				
				$sentencia -> bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
				$sentencia -> bindParam(':oferta_id', $oferta_id, PDO::PARAM_INT);
				$sentencia -> bindParam(':servicio_id', $servicio_id, PDO::PARAM_INT);
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
				$sql = "SELECT i.*, 
                               p.nombre as producto_nombre,
                               o.nombre as oferta_nombre,
                               s.nombre as servicio_nombre
						FROM imagenes i 
						LEFT JOIN productos p ON i.producto_id = p.id 
						LEFT JOIN ofertas o ON i.oferta_id = o.id
						LEFT JOIN servicios s ON i.servicio_id = s.id
						WHERE i.id = :id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				$sentencia -> execute();
				
				$resultado = $sentencia -> fetch();
				
				if(!empty($resultado)){
					$imagen = new Imagen(
                        $resultado['id'], 
                        $resultado['producto_id'], 
                        $resultado['oferta_id'], 
                        $resultado['servicio_id'], 
                        $resultado['path'], 
                        $resultado['fecha_creado'],
                        $resultado['es_principal'] ?? 0
                    );
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
                        $imagenes[] = new Imagen(
                            $fila['id'], 
                            $fila['producto_id'], 
                            $fila['oferta_id'], 
                            $fila['servicio_id'], 
                            $fila['path'], 
                            $fila['fecha_creado'], 
                            $fila['es_principal'] ?? 0
                        );
					}
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $imagenes;
	}

	public static function obtener_imagenes_por_oferta($conexion, $oferta_id){
		$imagenes = array();
		if(isset($conexion)){
			try{
                $sql = "SELECT * FROM imagenes WHERE oferta_id = :oferta_id ORDER BY es_principal DESC, fecha_creado";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':oferta_id', $oferta_id, PDO::PARAM_INT);
				$sentencia -> execute();
				$resultado = $sentencia -> fetchAll();
				
				if(count($resultado)){
					foreach($resultado as $fila){
                        $imagenes[] = new Imagen(
                            $fila['id'], 
                            $fila['producto_id'], 
                            $fila['oferta_id'], 
                            $fila['servicio_id'], 
                            $fila['path'], 
                            $fila['fecha_creado'], 
                            $fila['es_principal'] ?? 0
                        );
					}
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $imagenes;
	}

	public static function obtener_imagenes_por_servicio($conexion, $servicio_id){
		$imagenes = array();
		if(isset($conexion)){
			try{
                $sql = "SELECT * FROM imagenes WHERE servicio_id = :servicio_id ORDER BY es_principal DESC, fecha_creado";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':servicio_id', $servicio_id, PDO::PARAM_INT);
				$sentencia -> execute();
				$resultado = $sentencia -> fetchAll();
				
				if(count($resultado)){
					foreach($resultado as $fila){
                        $imagenes[] = new Imagen(
                            $fila['id'], 
                            $fila['producto_id'], 
                            $fila['oferta_id'], 
                            $fila['servicio_id'], 
                            $fila['path'], 
                            $fila['fecha_creado'], 
                            $fila['es_principal'] ?? 0
                        );
					}
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $imagenes;
	}

	public static function obtener_imagen_principal_por_entidad($conexion, $tipo_entidad, $entidad_id){
		$imagen = null;
		if(isset($conexion)){
			try{
				$campo_id = $tipo_entidad . '_id';
				$sql = "SELECT * FROM imagenes WHERE $campo_id = :entidad_id AND es_principal = 1 LIMIT 1";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':entidad_id', $entidad_id, PDO::PARAM_INT);
				$sentencia -> execute();
				
				$resultado = $sentencia -> fetch();
				
				if(!empty($resultado)){
					$imagen = new Imagen(
                        $resultado['id'], 
                        $resultado['producto_id'], 
                        $resultado['oferta_id'], 
                        $resultado['servicio_id'], 
                        $resultado['path'], 
                        $resultado['fecha_creado'],
                        $resultado['es_principal'] ?? 0
                    );
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $imagen;
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

	public static function eliminar_imagenes_por_oferta($conexion, $oferta_id){
		$eliminado = false;
		if(isset($conexion)){
			try{
				$sql = "DELETE FROM imagenes WHERE oferta_id = :oferta_id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':oferta_id', $oferta_id, PDO::PARAM_INT);
				$eliminado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $eliminado;
	}

	public static function eliminar_imagenes_por_servicio($conexion, $servicio_id){
		$eliminado = false;
		if(isset($conexion)){
			try{
				$sql = "DELETE FROM imagenes WHERE servicio_id = :servicio_id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':servicio_id', $servicio_id, PDO::PARAM_INT);
				$eliminado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $eliminado;
	}

	public static function establecer_imagen_principal($conexion, $imagen_id, $tipo_entidad, $entidad_id){
		$actualizado = false;
		if(isset($conexion)){
			try{
				// Primero, quitar principal de todas las imágenes de esta entidad
				$campo_id = $tipo_entidad . '_id';
				$sql_quitar_principal = "UPDATE imagenes SET es_principal = 0 WHERE $campo_id = :entidad_id";
				$sentencia_quitar = $conexion -> prepare($sql_quitar_principal);
				$sentencia_quitar -> bindParam(':entidad_id', $entidad_id, PDO::PARAM_INT);
				$sentencia_quitar -> execute();

				// Luego, establecer la imagen específica como principal
				$sql_establecer = "UPDATE imagenes SET es_principal = 1 WHERE id = :imagen_id";
				$sentencia_establecer = $conexion -> prepare($sql_establecer);
				$sentencia_establecer -> bindParam(':imagen_id', $imagen_id, PDO::PARAM_INT);
				$actualizado = $sentencia_establecer -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $actualizado;
	}
}
?>