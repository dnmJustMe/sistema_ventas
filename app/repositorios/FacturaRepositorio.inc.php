<?php
include_once '../entidades/Factura.inc.php';

class RepositorioFactura{
    
   public static function obtener_todos($conexion){
       $facturas = array(); 
       if(isset($conexion)){
           try{
               $sql = "SELECT f.*, fe.nombre as fecha_nombre 
                       FROM facturas f 
                       LEFT JOIN fechas fe ON f.fecha_id = fe.id 
                       ORDER BY f.fecha_creado DESC";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                       $factura = new Factura($fila['id'], $fila['nombre_cliente'], $fila['numero_telefono'], 
                                             $fila['fecha_id'], $fila['costo_total'], $fila['precio_venta_total'], 
                                             $fila['descuento'], $fila['fecha_creado']);
                       $factura->fecha_nombre = $fila['fecha_nombre'];
                       $facturas[] = $factura;
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $facturas;
   } 
    
    public static function obtener_numero_facturas($conexion){
       $total_facturas = null;
        if(isset($conexion)){
            try{
                $sql = "SELECT COUNT(*) as total FROM facturas";
                $sentencia = $conexion -> prepare($sql);
                $sentencia ->execute();
                $resultado = $sentencia -> fetch();
                $total_facturas = $resultado['total'];
            }catch(PDOException $ex){
                print 'ERROR' . $ex ->getMessage();
            }
        }
        return $total_facturas;
    }
	
	public static function insertar_factura($conexion, $factura){
		$factura_insertada = false;
		
		if(isset($conexion)){
			try{
				$sql = "INSERT INTO facturas(nombre_cliente, numero_telefono, fecha_id, costo_total, precio_venta_total, descuento) 
						VALUES(:nombre_cliente, :numero_telefono, :fecha_id, :costo_total, :precio_venta_total, :descuento)";
				
                $sentencia = $conexion -> prepare($sql);
                
                $nombre_cliente = $factura -> obtener_nombre_cliente();
                $numero_telefono = $factura -> obtener_numero_telefono();
                $fecha_id = $factura -> obtener_fecha_id();
                $costo_total = $factura -> obtener_costo_total();
                $precio_venta_total = $factura -> obtener_precio_venta_total();
                $descuento = $factura -> obtener_descuento();
				
				$sentencia -> bindParam(':nombre_cliente', $nombre_cliente, PDO::PARAM_STR);
				$sentencia -> bindParam(':numero_telefono', $numero_telefono, PDO::PARAM_STR);
				$sentencia -> bindParam(':fecha_id', $fecha_id, PDO::PARAM_INT);
				$sentencia -> bindParam(':costo_total', $costo_total);
				$sentencia -> bindParam(':precio_venta_total', $precio_venta_total);
				$sentencia -> bindParam(':descuento', $descuento);
				
				$factura_insertada = $sentencia -> execute();
				return $conexion->lastInsertId(); // Retorna el ID de la factura insertada
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
				return false;
			}
		}
		return false;
	}

	public static function agregar_producto_a_factura($conexion, $factura_id, $producto_id, $cantidad, $precio_unitario){
		$agregado = false;
		if(isset($conexion)){
			try{
				$sql = "INSERT INTO factura_productos(factura_id, producto_id, cantidad, precio_unitario) 
						VALUES(:factura_id, :producto_id, :cantidad, :precio_unitario)";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':factura_id', $factura_id, PDO::PARAM_INT);
				$sentencia -> bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
				$sentencia -> bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
				$sentencia -> bindParam(':precio_unitario', $precio_unitario);
				$agregado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $agregado;
	}

	public static function agregar_oferta_a_factura($conexion, $factura_id, $oferta_id, $cantidad, $precio_unitario){
		$agregado = false;
		if(isset($conexion)){
			try{
				$sql = "INSERT INTO factura_ofertas(factura_id, oferta_id, cantidad, precio_unitario) 
						VALUES(:factura_id, :oferta_id, :cantidad, :precio_unitario)";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':factura_id', $factura_id, PDO::PARAM_INT);
				$sentencia -> bindParam(':oferta_id', $oferta_id, PDO::PARAM_INT);
				$sentencia -> bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
				$sentencia -> bindParam(':precio_unitario', $precio_unitario);
				$agregado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $agregado;
	}
	
	public static function obtener_factura_por_id($conexion, $id){
		$factura = null;
		if(isset($conexion)){
			try{
				$sql = "SELECT f.*, fe.nombre as fecha_nombre 
						FROM facturas f 
						LEFT JOIN fechas fe ON f.fecha_id = fe.id 
						WHERE f.id = :id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				$sentencia -> execute();
				
				$resultado = $sentencia -> fetch();
				
				if(!empty($resultado)){
					$factura = new Factura($resultado['id'], $resultado['nombre_cliente'], $resultado['numero_telefono'], 
										  $resultado['fecha_id'], $resultado['costo_total'], $resultado['precio_venta_total'], 
										  $resultado['descuento'], $resultado['fecha_creado']);
					$factura->fecha_nombre = $resultado['fecha_nombre'];
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $factura;
	}

	public static function obtener_productos_por_factura($conexion, $factura_id){
		$productos = array();
		if(isset($conexion)){
			try{
				$sql = "SELECT p.*, fp.cantidad, fp.precio_unitario 
						FROM productos p 
						JOIN factura_productos fp ON p.id = fp.producto_id 
						WHERE fp.factura_id = :factura_id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':factura_id', $factura_id, PDO::PARAM_INT);
				$sentencia -> execute();
				$resultado = $sentencia -> fetchAll();
				
				if(count($resultado)){
					foreach($resultado as $fila){
						$producto = new Producto($fila['id'], $fila['nombre'], $fila['descripcion'], 
												$fila['costo'], $fila['precio_venta'], 
												$fila['categoria_id'], $fila['fecha_creado']);
						$producto->cantidad = $fila['cantidad'];
						$producto->precio_unitario = $fila['precio_unitario'];
						$productos[] = $producto;
					}
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $productos;
	}

	public static function obtener_ofertas_por_factura($conexion, $factura_id){
		$ofertas = array();
		if(isset($conexion)){
			try{
				$sql = "SELECT o.*, fo.cantidad, fo.precio_unitario 
						FROM ofertas o 
						JOIN factura_ofertas fo ON o.id = fo.oferta_id 
						WHERE fo.factura_id = :factura_id";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':factura_id', $factura_id, PDO::PARAM_INT);
				$sentencia -> execute();
				$resultado = $sentencia -> fetchAll();
				
				if(count($resultado)){
					foreach($resultado as $fila){
						$oferta = new Oferta($fila['id'], $fila['nombre'], $fila['descripcion'], 
											$fila['precio_final_venta'], $fila['fecha_creado']);
						$oferta->cantidad = $fila['cantidad'];
						$oferta->precio_unitario = $fila['precio_unitario'];
						$ofertas[] = $oferta;
					}
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $ofertas;
	}

	public static function obtener_ventas_por_fecha($conexion, $fecha_inicio, $fecha_fin){
		$facturas = array();
		if(isset($conexion)){
			try{
				$sql = "SELECT f.*, fe.nombre as fecha_nombre 
						FROM facturas f 
						LEFT JOIN fechas fe ON f.fecha_id = fe.id 
						WHERE f.fecha_creado BETWEEN :fecha_inicio AND :fecha_fin 
						ORDER BY f.fecha_creado DESC";
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
				$sentencia -> bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
				$sentencia -> execute();
				$resultado = $sentencia -> fetchAll();
				
				if(count($resultado)){
					foreach($resultado as $fila){
						$factura = new Factura($fila['id'], $fila['nombre_cliente'], $fila['numero_telefono'], 
											  $fila['fecha_id'], $fila['costo_total'], $fila['precio_venta_total'], 
											  $fila['descuento'], $fila['fecha_creado']);
						$factura->fecha_nombre = $fila['fecha_nombre'];
						$facturas[] = $factura;
					}
				}
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $facturas;
	}

	public static function obtener_total_ventas($conexion, $fecha_inicio = null, $fecha_fin = null){
		$total_ventas = 0;
		if(isset($conexion)){
			try{
				if($fecha_inicio && $fecha_fin){
					$sql = "SELECT SUM(precio_venta_total) as total FROM facturas WHERE fecha_creado BETWEEN :fecha_inicio AND :fecha_fin";
					$sentencia = $conexion -> prepare($sql);
					$sentencia -> bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
					$sentencia -> bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
				} else {
					$sql = "SELECT SUM(precio_venta_total) as total FROM facturas";
					$sentencia = $conexion -> prepare($sql);
				}
				$sentencia -> execute();
				$resultado = $sentencia -> fetch();
				$total_ventas = $resultado['total'] ? $resultado['total'] : 0;
			}catch(PDOException $ex){
				print 'ERROR' . $ex -> getMessage();
			}
		}
		return $total_ventas;
	}
    
    public static function buscar_factura_cliente($conexion, $busqueda){
       $facturas = array(); 
        $busqueda = '%'. $busqueda . '%';
       if(isset($conexion)){
           try{
               $sql = "SELECT f.*, fe.nombre as fecha_nombre 
                       FROM facturas f 
                       LEFT JOIN fechas fe ON f.fecha_id = fe.id 
                       WHERE f.nombre_cliente LIKE :busqueda OR f.numero_telefono LIKE :busqueda 
                       ORDER BY f.fecha_creado DESC";
               $sentencia = $conexion -> prepare($sql);
               $sentencia -> bindParam(':busqueda', $busqueda, PDO::PARAM_STR);
               $sentencia -> execute();
               $resultado = $sentencia -> fetchAll();
               
               if(count($resultado)){
                   foreach($resultado as $fila){
                       $factura = new Factura($fila['id'], $fila['nombre_cliente'], $fila['numero_telefono'], 
                                             $fila['fecha_id'], $fila['costo_total'], $fila['precio_venta_total'], 
                                             $fila['descuento'], $fila['fecha_creado']);
                       $factura->fecha_nombre = $fila['fecha_nombre'];
                       $facturas[] = $factura;
                   }
               }
               
           }catch (PDOException $ex){
               print "ERROR" . $ex -> getMessage();
           }
       }
       return $facturas;
   }

   public static function actualizar_factura($conexion, $id, $nombre_cliente, $numero_telefono, $fecha_id, $costo_total, $precio_venta_total, $descuento){
		$actualizado = false;
		if(isset($conexion)){
			try{
				$sql = "UPDATE facturas SET nombre_cliente = :nombre_cliente, numero_telefono = :numero_telefono, 
						fecha_id = :fecha_id, costo_total = :costo_total, precio_venta_total = :precio_venta_total, 
						descuento = :descuento WHERE id = :id";
				
				$sentencia = $conexion -> prepare($sql);
				$sentencia -> bindParam(':nombre_cliente', $nombre_cliente, PDO::PARAM_STR);
				$sentencia -> bindParam(':numero_telefono', $numero_telefono, PDO::PARAM_STR);
				$sentencia -> bindParam(':fecha_id', $fecha_id, PDO::PARAM_INT);
				$sentencia -> bindParam(':costo_total', $costo_total);
				$sentencia -> bindParam(':precio_venta_total', $precio_venta_total);
				$sentencia -> bindParam(':descuento', $descuento);
				$sentencia -> bindParam(':id', $id, PDO::PARAM_INT);
				
				$actualizado = $sentencia -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $actualizado;
	}

	public static function eliminar_factura($conexion, $id){
		$eliminado = false;
		if(isset($conexion)){
			try{
				// Primero eliminar los registros relacionados
				$sql1 = "DELETE FROM factura_productos WHERE factura_id = :id";
				$sentencia1 = $conexion -> prepare($sql1);
				$sentencia1 -> bindParam(':id', $id, PDO::PARAM_INT);
				$sentencia1 -> execute();

				$sql2 = "DELETE FROM factura_ofertas WHERE factura_id = :id";
				$sentencia2 = $conexion -> prepare($sql2);
				$sentencia2 -> bindParam(':id', $id, PDO::PARAM_INT);
				$sentencia2 -> execute();

				// Luego eliminar la factura
				$sql3 = "DELETE FROM facturas WHERE id = :id";
				$sentencia3 = $conexion -> prepare($sql3);
				$sentencia3 -> bindParam(':id', $id, PDO::PARAM_INT);
				$eliminado = $sentencia3 -> execute();
			}catch(PDOException $ex){
				print 'ERROR' . $ex->getMessage();
			}
		}
		return $eliminado;
	}
}
?>