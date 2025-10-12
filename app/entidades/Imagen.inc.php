<?php
class Imagen{
    private $id;
    private $producto_id;
    private $path;
    private $fecha_creado;
    
    public function __construct($id, $producto_id, $path, $fecha_creado){
        $this -> id = $id;
        $this -> producto_id = $producto_id;
        $this -> path = $path;
        $this -> fecha_creado = $fecha_creado;
    }
    
    public function obtener_id(){
        return $this -> id;
    }
    public function obtener_producto_id(){
        return $this -> producto_id;
    }
    public function obtener_path(){
        return $this -> path;
    }
    public function obtener_fecha_creado(){
        return $this -> fecha_creado;
    }
    
    public function cambiar_path($path){
        $this -> path = $path;
    }
}
?>