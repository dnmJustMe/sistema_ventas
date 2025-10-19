<?php
class Imagen{
    private $id;
    private $producto_id;
    private $oferta_id;
    private $servicio_id;
    private $path;
    private $fecha_creado;
    private $es_principal;
    
    public function __construct($id, $producto_id, $oferta_id, $servicio_id, $path, $fecha_creado, $es_principal = 0){
        $this->id = $id;
        $this->producto_id = $producto_id;
        $this->oferta_id = $oferta_id;
        $this->servicio_id = $servicio_id;
        $this->path = $path;
        $this->fecha_creado = $fecha_creado;
        $this->es_principal = (int)$es_principal;
    }
    
    public function obtener_id(){
        return $this->id;
    }
    
    public function obtener_producto_id(){
        return $this->producto_id;
    }
    
    public function obtener_oferta_id(){
        return $this->oferta_id;
    }
    
    public function obtener_servicio_id(){
        return $this->servicio_id;
    }
    
    public function obtener_entidad_id(){
        if ($this->producto_id) return $this->producto_id;
        if ($this->oferta_id) return $this->oferta_id;
        if ($this->servicio_id) return $this->servicio_id;
        return null;
    }
    
    public function obtener_tipo_entidad(){
        if ($this->producto_id) return 'producto';
        if ($this->oferta_id) return 'oferta';
        if ($this->servicio_id) return 'servicio';
        return null;
    }
    
    public function obtener_path(){
        return $this->path;
    }
    
    public function obtener_fecha_creado(){
        return $this->fecha_creado;
    }
    
    public function obtener_es_principal(){
        return $this->es_principal;
    }
    
    public function cambiar_path($path){
        $this->path = $path;
    }
    
    public function cambiar_es_principal($valor){
        $this->es_principal = (int)$valor;
    }
    
    public function establecer_entidad($tipo, $id){
        $this->producto_id = null;
        $this->oferta_id = null;
        $this->servicio_id = null;
        
        switch($tipo){
            case 'producto':
                $this->producto_id = $id;
                break;
            case 'oferta':
                $this->oferta_id = $id;
                break;
            case 'servicio':
                $this->servicio_id = $id;
                break;
        }
    }
}
?>