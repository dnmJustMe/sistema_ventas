<?php
include_once '../entidades/ConceptoAbonar.inc.php';

class RepositorioConceptoAbonar{
    
   public static function obtener_todos($conexion){
       $conceptos = array(); 
       if(isset($conexion)){
           try{
               $sql = "SELECT * FROM conceptos_abonar ORDER BY porcentaje DESC";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                       $conceptos[] = new ConceptoAbonar($fila['id'], $fila['nombre'], $fila['porcentaje'], $fila['fecha_creado']);
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $conceptos;
   } 
    
    public static function obtener_numero_conceptos($conexion){
       $total_conceptos = null;
        if(isset($conexion)){
            try{
                $sql = "SELECT COUNT(*) as total FROM conceptos_abonar";
                $sentencia = $conexion -> prepare($sql);
                $sentencia ->execute();
                $resultado = $sentencia -> fetch();
                $total_conceptos = $resultado['total'];
            }catch(PDOException $ex){
                print 'ERROR' . $ex ->getMessage();
            }
        }
        return $total_conceptos;
    }
	
	public static function insertar_concepto($conexion, $concepto){
		$concepto_insertado = false;
		
		if(isset($conexion)){
			try{
				$sql = "INSERT INTO conceptos_abonar(nombre, porcentaje) VALUES(:nombre, :porcentaje)";
				
                $sentencia = $conexion -> prepare($sql);
                
                $nombre = $concepto -> obtener_nombre();
                $porcentaje = $concepto -> obtener_porcentaje();
				
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> bindParam(':porcentaje', $porcentaje);
				
				$concepto_insertado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $concepto_insertado;
	}
	
	public static function obtener_concepto_por_id($conexion, $id){
		$concepto = null;
		if(isset($conexion)){
			try{
				$sql = "SELECT * FROM conceptos_abonar WHERE id = :id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				$sentencia -> execute();
				
				$resultado = $sentencia -> fetch();
				
				if(!empty($resultado)){
					$concepto = new ConceptoAbonar($resultado['id'], $resultado['nombre'], $resultado['porcentaje'], $resultado['fecha_creado']);
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $concepto;
	}

	public static function obtener_suma_porcentajes($conexion){
		$suma_porcentajes = 0;
		if(isset($conexion)){
			try{
				$sql = "SELECT SUM(porcentaje) as total FROM conceptos_abonar";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> execute();
				$resultado = $sentencia -> fetch();
				$suma_porcentajes = $resultado['total'] ? $resultado['total'] : 0;
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $suma_porcentajes;
	}
    
    public static function buscar_concepto_nombre($conexion, $busqueda){
       $conceptos = array(); 
        $busqueda = '%'. $busqueda . '%';
       if(isset($conexion)){
           try{
               $sql = "SELECT * FROM conceptos_abonar WHERE nombre LIKE :busqueda ORDER BY porcentaje DESC";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> bindParam(':busqueda', $busqueda, PDO::PARAM_STR);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                       $conceptos[] = new ConceptoAbonar($fila['id'], $fila['nombre'], $fila['porcentaje'], $fila['fecha_creado']);
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $conceptos;
   }

   public static function actualizar_concepto($conexion, $id, $nombre, $porcentaje){
		$actualizado = false;
		if(isset($conexion)){
			try{
				$sql = "UPDATE conceptos_abonar SET nombre = :nombre, porcentaje = :porcentaje WHERE id = :id";
				
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> bindParam(':porcentaje', $porcentaje);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				
				$actualizado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $actualizado;
	}

	public static function eliminar_concepto($conexion, $id){
		$eliminado = false;
		if(isset($conexion)){
			try{
				$sql = "DELETE FROM conceptos_abonar WHERE id = :id";
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