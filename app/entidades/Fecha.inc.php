<?php
class Fecha{
    private $id;
    private $nombre;
    private $descripcion;
    private $fecha;
    private $fecha_creado;
    
    public function __construct($id, $nombre, $descripcion, $fecha, $fecha_creado){
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> descripcion = $descripcion;
        $this -> fecha = $fecha;
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
    public function obtener_fecha(){
        return $this -> fecha;
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
    public function cambiar_fecha($fecha){
        $this -> fecha = $fecha;
    }
}
?>