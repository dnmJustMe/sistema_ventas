<?php
class ConceptoAbonar{
    private $id;
    private $nombre;
    private $porcentaje;
    private $fecha_creado;
    
    public function __construct($id, $nombre, $porcentaje, $fecha_creado){
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> porcentaje = $porcentaje;
        $this -> fecha_creado = $fecha_creado;
    }
    
    public function obtener_id(){
        return $this -> id;
    }
    public function obtener_nombre(){
        return $this -> nombre;
    }
    public function obtener_porcentaje(){
        return $this -> porcentaje;
    }
    public function obtener_fecha_creado(){
        return $this -> fecha_creado;
    }
    
    public function cambiar_nombre($nombre){
        $this -> nombre = $nombre;
    }
    public function cambiar_porcentaje($porcentaje){
        $this -> porcentaje = $porcentaje;
    }
}
?>