<?php
class Imagen{
    private $id;
    private $producto_id;
    private $path;
    private $fecha_creado;
    private $es_principal;
    
    public function __construct($id, $producto_id, $path, $fecha_creado, $es_principal = 0){
        $this -> id = $id;
        $this -> producto_id = $producto_id;
        $this -> path = $path;
        $this -> fecha_creado = $fecha_creado;
        $this -> es_principal = (int)$es_principal;
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
    public function obtener_es_principal(){
        return $this -> es_principal;
    }
    
    public function cambiar_path($path){
        $this -> path = $path;
    }
    public function cambiar_es_principal($valor){
        $this -> es_principal = (int)$valor;
    }
}
?>