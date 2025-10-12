<?php
class Factura{
    private $id;
    private $nombre_cliente;
    private $numero_telefono;
    private $fecha_id;
    private $costo_total;
    private $precio_venta_total;
    private $descuento;
    private $fecha_creado;
    
    public function __construct($id, $nombre_cliente, $numero_telefono, $fecha_id, $costo_total, $precio_venta_total, $descuento, $fecha_creado){
        $this -> id = $id;
        $this -> nombre_cliente = $nombre_cliente;
        $this -> numero_telefono = $numero_telefono;
        $this -> fecha_id = $fecha_id;
        $this -> costo_total = $costo_total;
        $this -> precio_venta_total = $precio_venta_total;
        $this -> descuento = $descuento;
        $this -> fecha_creado = $fecha_creado;
    }
    
    public function obtener_id(){
        return $this -> id;
    }
    public function obtener_nombre_cliente(){
        return $this -> nombre_cliente;
    }
    public function obtener_numero_telefono(){
        return $this -> numero_telefono;
    }
    public function obtener_fecha_id(){
        return $this -> fecha_id;
    }
    public function obtener_costo_total(){
        return $this -> costo_total;
    }
    public function obtener_precio_venta_total(){
        return $this -> precio_venta_total;
    }
    public function obtener_descuento(){
        return $this -> descuento;
    }
    public function obtener_fecha_creado(){
        return $this -> fecha_creado;
    }
    
    public function cambiar_nombre_cliente($nombre_cliente){
        $this -> nombre_cliente = $nombre_cliente;
    }
    public function cambiar_numero_telefono($numero_telefono){
        $this -> numero_telefono = $numero_telefono;
    }
    public function cambiar_fecha_id($fecha_id){
        $this -> fecha_id = $fecha_id;
    }
    public function cambiar_costo_total($costo_total){
        $this -> costo_total = $costo_total;
    }
    public function cambiar_precio_venta_total($precio_venta_total){
        $this -> precio_venta_total = $precio_venta_total;
    }
    public function cambiar_descuento($descuento){
        $this -> descuento = $descuento;
    }
}
?>