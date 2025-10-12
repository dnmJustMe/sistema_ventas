<?php
include_once '../entidades/Servicio.inc.php';

class RepositorioServicio{
    
   public static function obtener_todos($conexion){
       $servicios = array(); 
       if(isset($conexion)){
           try{
               $sql = "SELECT * FROM servicios ORDER BY nombre";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                       $servicios[] = new Servicio($fila['id'], $fila['nombre'], $fila['descripcion'], $fila['fecha_creado']);
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $servicios;
   } 
    
    public static function obtener_numero_servicios($conexion){
       $total_servicios = null;
        if(isset($conexion)){
            try{
                $sql = "SELECT COUNT(*) as total FROM servicios";
                $sentencia = $conexion -> prepare($sql);
                $sentencia ->execute();
                $resultado = $sentencia -> fetch();
                $total_servicios = $resultado['total'];
            }catch(PDOException $ex){
                print 'ERROR' . $ex ->getMessage();
            }
        }
        return $total_servicios;
    }
	
	public static function insertar_servicio($conexion, $servicio){
		$servicio_insertado = false;
		
		if(isset($conexion)){
			try{
				$sql = "INSERT INTO servicios(nombre, descripcion) VALUES(:nombre, :descripcion)";
				
                $sentencia = $conexion -> prepare($sql);
                
                $nombre = $servicio -> obtener_nombre();
                $descripcion = $servicio -> obtener_descripcion();
				
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
				
				$servicio_insertado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $servicio_insertado;
	}
	
	public static function nombre_existe($conexion, $nombre){
		$nombre_existe = false;
		
		if(isset($conexion)){
			try{
				$sql = "SELECT * FROM servicios WHERE nombre = :nombre";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> execute();
				
				$resultado = $sentencia -> fetchAll();
				
				if(count($resultado)){
					$nombre_existe = true;
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $nombre_existe;
	} 
	
	public static function obtener_servicio_por_id($conexion, $id){
		$servicio = null;
		if(isset($conexion)){
			try{
				$sql = "SELECT * FROM servicios WHERE id = :id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				$sentencia -> execute();
				
				$resultado = $sentencia -> fetch();
				
				if(!empty($resultado)){
					$servicio = new Servicio($resultado['id'], $resultado['nombre'], $resultado['descripcion'], $resultado['fecha_creado']);
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $servicio;
	}
    
    public static function buscar_servicio_nombre($conexion, $busqueda){
       $servicios = array(); 
        $busqueda = '%'. $busqueda . '%';
       if(isset($conexion)){
           try{
               $sql = "SELECT * FROM servicios WHERE nombre LIKE :busqueda OR descripcion LIKE :busqueda ORDER BY nombre";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> bindParam(':busqueda', $busqueda, PDO::PARAM_STR);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                       $servicios[] = new Servicio($fila['id'], $fila['nombre'], $fila['descripcion'], $fila['fecha_creado']);
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $servicios;
   }

   public static function actualizar_servicio($conexion, $id, $nombre, $descripcion){
		$actualizado = false;
		if(isset($conexion)){
			try{
				$sql = "UPDATE servicios SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
				
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				
				$actualizado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $actualizado;
	}

	public static function eliminar_servicio($conexion, $id){
		$eliminado = false;
		if(isset($conexion)){
			try{
				$sql = "DELETE FROM servicios WHERE id = :id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				$eliminado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $eliminado;
	}
}
?>