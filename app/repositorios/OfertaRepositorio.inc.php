<?php
include_once '../entidades/Oferta.inc.php';

class RepositorioOferta{
    
   public static function obtener_todos($conexion){
       $ofertas = array(); 
       if(isset($conexion)){
           try{
               $sql = "SELECT * FROM ofertas ORDER BY nombre";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                       $ofertas[] = new Oferta($fila['id'], $fila['nombre'], $fila['descripcion'], 
                                              $fila['precio_final_venta'], $fila['fecha_creado']);
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $ofertas;
   } 
    
    public static function obtener_numero_ofertas($conexion){
       $total_ofertas = null;
        if(isset($conexion)){
            try{
                $sql = "SELECT COUNT(*) as total FROM ofertas";
                $sentencia = $conexion -> prepare($sql);
                $sentencia ->execute();
                $resultado = $sentencia -> fetch();
                $total_ofertas = $resultado['total'];
            }catch(PDOException $ex){
                print 'ERROR' . $ex ->getMessage();
            }
        }
        return $total_ofertas;
    }
	
	public static function insertar_oferta($conexion, $oferta){
		$oferta_insertada = false;
		
		if(isset($conexion)){
			try{
				$sql = "INSERT INTO ofertas(nombre, descripcion, precio_final_venta) 
						VALUES(:nombre, :descripcion, :precio_final_venta)";
				
                $sentencia = $conexion -> prepare($sql);
                
                $nombre = $oferta -> obtener_nombre();
                $descripcion = $oferta -> obtener_descripcion();
                $precio_final_venta = $oferta -> obtener_precio_final_venta();
				
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
				$sentencia -> bindParam(':precio_final_venta', $precio_final_venta);
				
				$oferta_insertada = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $oferta_insertada;
	}
	
	public static function nombre_existe($conexion, $nombre){
		$nombre_existe = false;
		
		if(isset($conexion)){
			try{
				$sql = "SELECT * FROM ofertas WHERE nombre = :nombre";
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
	
	public static function obtener_oferta_por_id($conexion, $id){
		$oferta = null;
		if(isset($conexion)){
			try{
				$sql = "SELECT * FROM ofertas WHERE id = :id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				$sentencia -> execute();
				
				$resultado = $sentencia -> fetch();
				
				if(!empty($resultado)){
					$oferta = new Oferta($resultado['id'], $resultado['nombre'], $resultado['descripcion'], 
										$resultado['precio_final_venta'], $resultado['fecha_creado']);
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $oferta;
	}

	public static function obtener_productos_por_oferta($conexion, $oferta_id){
		$productos = array();
		if(isset($conexion)){
			try{
				$sql = "SELECT p.* 
						FROM productos p 
						JOIN oferta_productos op ON p.id = op.producto_id 
						WHERE op.oferta_id = :oferta_id 
						ORDER BY p.nombre";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':oferta_id', $oferta_id, PDO::PARAM_INT);
				$sentencia -> execute();
				$resultado = $sentencia -> fetchAll();
				
				if(count($resultado)){
					foreach($resultado as $fila){
						$productos[] = new Producto($fila['id'], $fila['nombre'], $fila['descripcion'], 
												   $fila['costo'], $fila['precio_venta'], 
												   $fila['categoria_id'], $fila['fecha_creado']);
					}
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $productos;
	}

	public static function agregar_producto_a_oferta($conexion, $oferta_id, $producto_id){
		$agregado = false;
		if(isset($conexion)){
			try{
				$sql = "INSERT INTO oferta_productos(oferta_id, producto_id) VALUES(:oferta_id, :producto_id)";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':oferta_id', $oferta_id, PDO::PARAM_INT);
				$sentencia -> bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
				$agregado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $agregado;
	}

	public static function eliminar_producto_de_oferta($conexion, $oferta_id, $producto_id){
		$eliminado = false;
		if(isset($conexion)){
			try{
				$sql = "DELETE FROM oferta_productos WHERE oferta_id = :oferta_id AND producto_id = :producto_id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':oferta_id', $oferta_id, PDO::PARAM_INT);
				$sentencia -> bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
				$eliminado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $eliminado;
	}
    
    public static function buscar_oferta_nombre($conexion, $busqueda){
       $ofertas = array(); 
        $busqueda = '%'. $busqueda . '%';
       if(isset($conexion)){
           try{
               $sql = "SELECT * FROM ofertas WHERE nombre LIKE :busqueda OR descripcion LIKE :busqueda ORDER BY nombre";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> bindParam(':busqueda', $busqueda, PDO::PARAM_STR);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                       $ofertas[] = new Oferta($fila['id'], $fila['nombre'], $fila['descripcion'], 
                                              $fila['precio_final_venta'], $fila['fecha_creado']);
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $ofertas;
   }

   public static function actualizar_oferta($conexion, $id, $nombre, $descripcion, $precio_final_venta){
		$actualizado = false;
		if(isset($conexion)){
			try{
				$sql = "UPDATE ofertas SET nombre = :nombre, descripcion = :descripcion, 
						precio_final_venta = :precio_final_venta WHERE id = :id";
				
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':nombre', $nombre, PDO::PARAM_STR);
				$sentencia -> bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
				$sentencia -> bindParam(':precio_final_venta', $precio_final_venta);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				
				$actualizado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $actualizado;
	}

	public static function eliminar_oferta($conexion, $id){
		$eliminado = false;
		if(isset($conexion)){
			try{
				$sql = "DELETE FROM ofertas WHERE id = :id";
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