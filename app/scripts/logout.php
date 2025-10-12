<?php
include_once 'app/util/ControlSesion.inc.php';

ControlSesion::cerrar_sesion();
header('Location: ' . SERVIDOR);
exit;
?>