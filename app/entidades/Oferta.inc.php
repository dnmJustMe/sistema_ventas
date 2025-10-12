<?php
class Oferta{
    private $id;
    private $nombre;
    private $descripcion;
    private $precio_final_venta;
    private $fecha_creado;
    
    public function __construct($id, $nombre, $descripcion, $precio_final_venta, $fecha_creado){
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> descripcion = $descripcion;
        $this -> precio_final_venta = $precio_final_venta;
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
    public function obtener_precio_final_venta(){
        return $this -> precio_final_venta;
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
    public function cambiar_precio_final_venta($precio_final_venta){
        $this -> precio_final_venta = $precio_final_venta;
    }
}
?>