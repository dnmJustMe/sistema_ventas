<?php
header($_SERVER['SERVER_PROTOCOL'] . "404 Not Found", true, 404);
$titulo = 'notJustPrint - Página No Encontrada';

include_once 'plantillas/html_declaracion.inc.php';
?>

<body class="error-body">
    <div class="error-container">
        <div class="error-content">
            <!-- Logo -->
            <div class="error-logo">
                <img src="<?php echo RUTA_IMG ?>horizontal.png" alt="notJustPrint" class="brand-logo-error">
            </div>

            <div class="error-code">404</div>
            <h1 class="error-title">Página No Encontrada</h1>
            <p class="error-message">Lo sentimos, la página que buscas no existe o ha sido movida.</p>

            <div class="error-actions">
                <a href="<?php echo SERVIDOR ?>" class="btn-home">
                    <i class="fas fa-home"></i> Volver al Inicio
                </a>
                <a href="javascript:history.back()" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Volver Atrás
                </a>
            </div>
        </div>

        <div class="error-graphic">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>
</body>

<?php
include_once 'plantillas/html_cierre.inc.php';
?>