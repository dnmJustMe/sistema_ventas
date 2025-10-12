<?php
class Usuario{
    private $id;
    private $nombre;
    private $password;
    private $fecha_creado;
    
    public function __construct($id, $nombre, $password, $fecha_creado){
           $this -> id = $id;
           $this -> nombre = $nombre;
           $this -> password = $password;
           $this -> fecha_creado = $fecha_creado;
       }
    
    public function obtener_id(){
        return $this -> id;
    }
    public function obtener_nombre(){
        return $this -> nombre;
    }
    public function obtener_password(){
        return $this -> password;
    }
    public function obtener_fecha_creado(){
        return $this -> fecha_creado;
    }
    
    public function cambiar_nombre($nombre){
        $this -> nombre = $nombre;
    }
    public function cambiar_password($password){
        $this -> password = $password;
    }
}
?>