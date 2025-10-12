<?php
class Conexion {
    private static $conexion;
    
    public static function abrir_conexion(){
        if(!isset(self::$conexion)){
           try{
               include_once 'config.inc.php';
               
               // Log de diagnóstico
               error_log("🔌 Conectando a BD: " . NOMBRE_SERVIDOR . " - " . NOMBRE_BD);
               
                self::$conexion = new PDO('mysql:host='.NOMBRE_SERVIDOR.'; dbname='.NOMBRE_BD, NOMBRE_USUARIO ,PASSWORD );
                self::$conexion -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conexion -> exec("SET CHARACTER SET utf8");              
               
               // Log de éxito
               error_log("✅ Conexión exitosa a la base de datos");
               
           } catch(PDOException $ex){
               $error_msg = "ERROR de conexión: " . $ex -> getMessage();
               error_log($error_msg);
               print $error_msg . "<br>";
               die();
           }
        }
    }
    
    public static function cerrar_conexion(){
        if (isset(self::$conexion)){
            self::$conexion = null;
            error_log("🔴 Conexión cerrada");
        }
    }
    
    public static function obtener_conexion(){
        if (!isset(self::$conexion)) {
            self::abrir_conexion();
        }
        return self::$conexion;
    }
}