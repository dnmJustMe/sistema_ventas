<?php
$titulo = 'notJustPrint - Iniciar Sesión';
include_once 'plantillas/html_declaracion.inc.php';
?>

<div class="login-body">
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="login-logo">
                <img src="<?php echo RUTA_IMG ?>horizontal.png" alt="notJustPrint" class="brand-logo-login">
            </div>

            <h2 class="login-title">Iniciar Sesión</h2>
            <p class="login-subtitle">Acceso administrativo</p>

            <form id="loginForm" class="login-form" action="procesar_login" method="POST">
                <div class="form-group">
                    <label for="usuario" class="form-label">
                        <i class="fas fa-user"></i> Usuario
                    </label>
                    <input type="text" id="usuario" name="usuario" class="form-control" required placeholder="Ingresa tu usuario">
                    <div class="field-error" id="usuario-error" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                    <div class="field-error" id="password-error" style="display: none;"></div>
                </div>

                <button type="submit" class="btn-login" id="submit-btn">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>

            <div class="login-footer">
                <a href="<?php echo SERVIDOR ?>" class="back-home">
                    <i class="fas fa-arrow-left"></i> Volver al inicio
                </a>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const $loginForm = $('#loginForm');
            const $submitBtn = $('#submit-btn');
            const $originalBtnText = $submitBtn.html();
            
            // Limpiar errores
            function clearErrors() {
                $('.field-error').hide().text('');
                $('.form-group').removeClass('error');
            }
            
            // Mostrar error en campo específico
            function showFieldError(field, message) {
                clearFieldError(field);
                $(`[name="${field}"]`).closest('.form-group').addClass('error');
                $(`#${field}-error`).text(message).show();
            }
            
            // Limpiar error de campo específico
            function clearFieldError(field) {
                $(`[name="${field}"]`).closest('.form-group').removeClass('error');
                $(`#${field}-error`).hide().text('');
            }
            
            // Validación del formulario
            function validateForm() {
                let isValid = true;
                const $inputs = $loginForm.find('[required]');
                
                $inputs.each(function() {
                    const $input = $(this);
                    const value = $input.val().trim();
                    const fieldName = $input.attr('name');
                    
                    if (!value) {
                        showFieldError(fieldName, 'Este campo es requerido');
                        isValid = false;
                    } else {
                        clearFieldError(fieldName);
                    }
                });
                
                return isValid;
            }
            
            // Estado de carga del botón
            function setLoading(loading) {
                if (loading) {
                    $submitBtn.prop('disabled', true);
                    $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
                } else {
                    $submitBtn.prop('disabled', false);
                    $submitBtn.html($originalBtnText);
                }
            }
            
            // Manejar envío del formulario
            $loginForm.on('submit', function(e) {
                e.preventDefault();
                
                clearErrors();
                
                if (!validateForm()) {
                    return;
                }
                
                setLoading(true);
                
                // Obtener datos del formulario
                const formData = new FormData(this);
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        console.log('Respuesta del login:', response);
                        
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message || 'Inicio de sesión exitoso',
                                confirmButtonColor: '#3AC47D',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Redireccionar
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    window.location.href = 'dashboard';
                                }
                            });
                        } else {
                            // Mostrar error específico del campo
                            if (response.field && response.field !== 'general') {
                                showFieldError(response.field, response.message);
                            } else {
                                // Error general
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de login',
                                    text: response.message,
                                    confirmButtonColor: '#3AC47D'
                                });
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la petición:', error);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo conectar con el servidor. Intente nuevamente.',
                            confirmButtonColor: '#3AC47D'
                        });
                    },
                    complete: function() {
                        setLoading(false);
                    }
                });
            });
            
            // Validación en tiempo real
            $loginForm.find('input').on('input', function() {
                const fieldName = $(this).attr('name');
                clearFieldError(fieldName);
            });
            
            console.log('✅ Login form handler initialized with jQuery');
        });
    </script>
</div>

<?php
include_once 'plantillas/html_cierre.inc.php';
?>