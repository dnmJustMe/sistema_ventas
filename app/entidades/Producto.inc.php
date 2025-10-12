<?php
class Producto{
    private $id;
    private $nombre;
    private $descripcion;
    private $costo;
    private $precio_venta;
    private $categoria_id;
    private $fecha_creado;
    
    public function __construct($id, $nombre, $descripcion, $costo, $precio_venta, $categoria_id, $fecha_creado){
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> descripcion = $descripcion;
        $this -> costo = $costo;
        $this -> precio_venta = $precio_venta;
        $this -> categoria_id = $categoria_id;
        $this -> fecha_creado = $fecha_creado;
    }
    
    public function obtener_id(){
        return $this -> id;
    }
    public function obtener_nombre(){
        return $this -> nombre;
    }
    public function obtener_descripcion(){
        return $this -> descripcion;
    }
    public function obtener_costo(){
        return $this -> costo;
    }
    public function obtener_precio_venta(){
        return $this -> precio_venta;
    }
    public function obtener_categoria_id(){
        return $this -> categoria_id;
    }
    public function obtener_fecha_creado(){
        return $this -> fecha_creado;
    }
    
    public function cambiar_nombre($nombre){
        $this -> nombre = $nombre;
    }
    public function cambiar_descripcion($descripcion){
        $this -> descripcion = $descripcion;
    }
    public function cambiar_costo($costo){
        $this -> costo = $costo;
    }
    public function cambiar_precio_venta($precio_venta){
        $this -> precio_venta = $precio_venta;
    }
    public function cambiar_categoria_id($categoria_id){
        $this -> categoria_id = $categoria_id;
    }
}
?>