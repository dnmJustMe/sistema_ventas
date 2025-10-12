<?php
class Categoria{
    private $id;
    private $nombre;
    private $fecha_creado;
    
    public function __construct($id, $nombre, $fecha_creado){
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> fecha_creado = $fecha_creado;
    }
    
    public function obtener_id(){
        return $this -> id;
    }
    public function obtener_nombre(){
        return $this -> nombre;
    }
    public function obtener_fecha_creado(){
        return $this -> fecha_creado;
    }
    
    public function cambiar_nombre($nombre){
        $this -> nombre = $nombre;
    }
}
?>