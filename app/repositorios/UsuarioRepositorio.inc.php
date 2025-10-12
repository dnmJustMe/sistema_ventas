<?php
include_once '../entidades/Usuario.inc.php';

class RepositorioUsuario{
    
   public static function obtener_todos($conexion){
       $usuarios = array(); 
       if(isset($conexion)){
           try{
               $sql = "SELECT * FROM users ORDER BY nombre";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                       $usuarios[] = new Usuario($fila['id'], $fila['nombre'], $fila['password'], $fila['fecha_creado']);
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $usuarios;
   } 
    
    public static function obtener_numero_usuarios($conexion){
       $total_usuarios = null;
        if(isset($conexion)){
            try{
                $sql = "SELECT COUNT(*) as total FROM users";
                $sentencia = $conexion -> prepare($sql);
                $sentencia ->execute();
                $resultado = $sentencia -> fetch();
                $total_usuarios = $resultado['total'];
            }catch(PDOException $ex){
                print 'ERROR' . $ex ->getMessage();
            }
        }
        return $total_usuarios;
    }
	
	public static function insertar_usuario($conexion, $usuario){
		$usuario_insertado = false;
		
		if(isset($conexion)){
			try{
				$sql = "INSERT INTO users(nombre, password) VALUES(:nombre, :password)";
				
                $sentencia = $conexion -> prepare($sql);
                
                $nombre = $usuario -> obtener_nombre();
                $password = $usuario -> obtener_password();
				
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> bindParam(':password', $password, PDO::PARAM_STR);
				
				$usuario_insertado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $usuario_insertado;
	}
	
	public static function nombre_existe($conexion, $nombre){
		$nombre_existe = true;
		
		if(isset($conexion)){
			try{
				$sql = "SELECT * FROM users WHERE nombre = :nombre";
				
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> execute();
				
				$resultado = $sentencia -> fetchAll();
				
				if(count($resultado)){
					$nombre_existe = true;
				}else{
					$nombre_existe = false;
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $nombre_existe;
	} 
	
	public static function obtener_usuario_por_nombre($conexion, $nombre){
		$usuario = null;
		if(isset($conexion)){
			try{
				include_once 'Usuario.inc.php';
				
				$sql = "SELECT * FROM users WHERE nombre = :nombre";
				
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> execute();
				
				$resultado = $sentencia -> fetch();
				
				if(!empty($resultado)){
					$usuario = new Usuario($resultado['id'], $resultado['nombre'],
										  $resultado['password'], $resultado['fecha_creado']);
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $usuario;
	}
	
	public static function obtener_usuario_por_id($conexion, $id){
		$usuario = null;
		if(isset($conexion)){
			try{
				include_once 'Usuario.inc.php';
				
				$sql = "SELECT * FROM users WHERE id = :id";
				
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_STR);
				$sentencia -> execute();
				
				$resultado = $sentencia -> fetch();
				
				if(!empty($resultado)){
					$usuario = new Usuario($resultado['id'], $resultado['nombre'],
											$resultado['password'], $resultado['fecha_creado']);
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $usuario;
	}
    
    public static function buscar_usuario_nombre($conexion, $busqueda){
       $usuarios = array(); 
        $busqueda = '%'. $busqueda . '%';
       if(isset($conexion)){
           try{
               $sql = "SELECT * FROM users WHERE nombre LIKE :busqueda ORDER BY nombre";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> bindParam(':busqueda', $busqueda, PDO::PARAM_STR);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                       $usuarios[] = new Usuario($fila['id'], $fila['nombre'], $fila['password'], $fila['fecha_creado']);
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $usuarios;
   }

   public static function actualizar_usuario($conexion, $id, $nombre, $password){
		$actualizado = false;
		if(isset($conexion)){
			try{
				$sql = "UPDATE users SET nombre = :nombre, password = :password WHERE id = :id";
				
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> bindParam(':password', $password, PDO::PARAM_STR);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				
				$actualizado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $actualizado;
	}

	public static function eliminar_usuario($conexion, $id){
		$eliminado = false;
		if(isset($conexion)){
			try{
				$sql = "DELETE FROM users WHERE id = :id";
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