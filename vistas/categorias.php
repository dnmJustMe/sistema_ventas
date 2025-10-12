<?php
$titulo = 'notJustPrint - Categorías';
$menu_activo = 'categorias';

include_once 'plantillas/html_declaracion.inc.php';
?>

<div class="dashboard-body">
    <?php include_once 'plantillas/navbar_dashboard.inc.php'; ?>

    <div class="dashboard-container">
        <main class="dashboard-content">
            <div class="content-header">
                <h1>Gestión de Categorías</h1>
                <p>Administra las categorías de productos</p>
            </div>

            <!-- Barra de herramientas -->
            <div class="toolbar">
                <button class="btn btn-primary" id="btn-agregar-categoria">
                    <i class="fas fa-plus"></i> Agregar Categoría
                </button>
            </div>

            <!-- Tabla de categorías -->
            <div class="table-container">
                <table class="table table-striped" id="tabla-categorias">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Fecha Creación</th>
                            <th>Total Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables manejará el contenido automáticamente -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Modal para agregar/editar categoría -->
<div class="modal" id="modal-categoria">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="titulo-modal-categoria">Agregar Categoría</h3>
            <button class="modal-close" id="cerrar-modal-categoria">&times;</button>
        </div>
        <form id="form-categoria">
            <div class="modal-body">
                <input type="hidden" id="categoria-id">

                <div class="form-group">
                    <label for="categoria-nombre">Nombre *</label>
                    <input type="text" id="categoria-nombre" name="nombre" required>
                    <small class="form-text">El nombre debe ser único</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelar-categoria">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="guardar-categoria">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal" id="modal-confirmar-eliminar-categoria">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3>Confirmar Eliminación</h3>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro de que deseas eliminar esta categoría?</p>
            <p class="text-warning">Solo se pueden eliminar categorías sin productos asociados.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelar-eliminar-categoria">Cancelar</button>
            <button type="button" class="btn btn-danger" id="confirmar-eliminar-categoria">Eliminar</button>
        </div>
    </div>
</div>

<script src="<?php echo RUTA_JS ?>categorias.js"></script>

<?php
include_once 'plantillas/html_cierre.inc.php';
?>