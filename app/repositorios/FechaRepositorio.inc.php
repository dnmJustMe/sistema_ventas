<?php
include_once '../entidades/Fecha.inc.php';

class RepositorioFecha{
    
   public static function obtener_todos($conexion){
       $fechas = array(); 
       if(isset($conexion)){
           try{
               $sql = "SELECT * FROM fechas ORDER BY fecha DESC";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                       $fechas[] = new Fecha($fila['id'], $fila['nombre'], $fila['descripcion'], 
                                            $fila['fecha'], $fila['fecha_creado']);
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $fechas;
   } 
    
    public static function obtener_numero_fechas($conexion){
       $total_fechas = null;
        if(isset($conexion)){
            try{
                $sql = "SELECT COUNT(*) as total FROM fechas";
                $sentencia = $conexion -> prepare($sql);
                $sentencia ->execute();
                $resultado = $sentencia -> fetch();
                $total_fechas = $resultado['total'];
            }catch(PDOException $ex){
                print 'ERROR' . $ex ->getMessage();
            }
        }
        return $total_fechas;
    }
	
	public static function insertar_fecha($conexion, $fecha){
		$fecha_insertada = false;
		
		if(isset($conexion)){
			try{
				$sql = "INSERT INTO fechas(nombre, descripcion, fecha) VALUES(:nombre, :descripcion, :fecha)";
				
                $sentencia = $conexion -> prepare($sql);
                
                $nombre = $fecha -> obtener_nombre();
                $descripcion = $fecha -> obtener_descripcion();
                $fecha_valor = $fecha -> obtener_fecha();
				
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
				$sentencia -> bindParam(':fecha', $fecha_valor, PDO::PARAM_STR);
				
				$fecha_insertada = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $fecha_insertada;
	}
	
	public static function obtener_fecha_por_id($conexion, $id){
		$fecha = null;
		if(isset($conexion)){
			try{
				$sql = "SELECT * FROM fechas WHERE id = :id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				$sentencia -> execute();
				
				$resultado = $sentencia -> fetch();
				
				if(!empty($resultado)){
					$fecha = new Fecha($resultado['id'], $resultado['nombre'], $resultado['descripcion'], 
									  $resultado['fecha'], $resultado['fecha_creado']);
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $fecha;
	}

	public static function obtener_fechas_proximas($conexion, $dias = 30){
		$fechas = array();
		if(isset($conexion)){
			try{
				$sql = "SELECT * FROM fechas WHERE fecha BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :dias DAY) ORDER BY fecha";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':dias', $dias, PDO::PARAM_INT);
				$sentencia -> execute();
				$resultado = $sentencia -> fetchAll();
				
				if(count($resultado)){
					foreach($resultado as $fila){
						$fechas[] = new Fecha($fila['id'], $fila['nombre'], $fila['descripcion'], 
											$fila['fecha'], $fila['fecha_creado']);
					}
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $fechas;
	}
    
    public static function buscar_fecha_nombre($conexion, $busqueda){
       $fechas = array(); 
        $busqueda = '%'. $busqueda . '%';
       if(isset($conexion)){
           try{
               $sql = "SELECT * FROM fechas WHERE nombre LIKE :busqueda OR descripcion LIKE :busqueda ORDER BY fecha DESC";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> bindParam(':busqueda', $busqueda, PDO::PARAM_STR);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                       $fechas[] = new Fecha($fila['id'], $fila['nombre'], $fila['descripcion'], 
                                            $fila['fecha'], $fila['fecha_creado']);
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $fechas;
   }

   public static function actualizar_fecha($conexion, $id, $nombre, $descripcion, $fecha){
		$actualizado = false;
		if(isset($conexion)){
			try{
				$sql = "UPDATE fechas SET nombre = :nombre, descripcion = :descripcion, fecha = :fecha WHERE id = :id";
				
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
				$sentencia -> bindParam(':fecha', $fecha, PDO::PARAM_STR);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				
				$actualizado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $actualizado;
	}

	public static function eliminar_fecha($conexion, $id){
		$eliminado = false;
		if(isset($conexion)){
			try{
				$sql = "DELETE FROM fechas WHERE id = :id";
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