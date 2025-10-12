<?php
$titulo = 'notJustPrint - Productos';
$menu_activo = 'productos';

include_once 'plantillas/html_declaracion.inc.php';
?>

<div class="dashboard-body">
    <?php include_once 'plantillas/navbar_dashboard.inc.php'; ?>

    <div class="dashboard-container">
        <main class="dashboard-content">
            <div class="content-header">
                <h1>Gestión de Productos</h1>
                <p>Administra los productos del catálogo</p>
            </div>

            <!-- Barra de herramientas -->
            <div class="toolbar">
                <button class="btn btn-primary" id="btn-agregar-producto">
                    <i class="fas fa-plus"></i> Agregar Producto
                </button>
            </div>

            <!-- Tabla de productos -->
            <div class="table-container">
                <table class="table table-striped" id="tabla-productos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Costo</th>
                            <th>Precio Venta</th>
                            <th>Categoría</th>
                            <th>Fecha Creación</th>
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

<!-- Modal para agregar/editar producto -->
<div class="modal" id="modal-producto">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="titulo-modal-producto">Agregar Producto</h3>
            <button class="modal-close" id="cerrar-modal-producto">&times;</button>
        </div>
        <form id="form-producto">
            <div class="modal-body">
                <input type="hidden" id="producto-id">

                <div class="form-group">
                    <label for="producto-nombre">Nombre *</label>
                    <input type="text" id="producto-nombre" name="nombre" required>
                </div>

                <div class="form-group">
                    <label for="producto-descripcion">Descripción</label>
                    <textarea id="producto-descripcion" name="descripcion" rows="3"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="producto-costo">Costo *</label>
                        <input type="number" id="producto-costo" name="costo" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="producto-precio">Precio Venta *</label>
                        <input type="number" id="producto-precio" name="precio_venta" step="0.01" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="producto-categoria">Categoría *</label>
                    <select id="producto-categoria" name="categoria_id" required>
                        <option value="">Seleccionar categoría...</option>
                        <!-- Las categorías se cargarán via AJAX -->
                    </select>
                </div>

                <div class="form-group">
                    <label for="producto-imagenes">Imágenes</label>
                    <input type="file" id="producto-imagenes" name="imagenes[]" accept="image/*" multiple>
                    <small>JPG, PNG, WEBP o GIF. Máx 5 MB c/u.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelar-producto">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="guardar-producto">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de detalles del producto -->
<div class="modal" id="modal-detalles-producto">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detalles del Producto</h3>
            <button class="modal-close" id="cerrar-modal-detalles">&times;</button>
        </div>
        <div class="modal-body">
            <div class="detalles-container">
                <div class="detalle-item">
                    <label>ID:</label>
                    <span id="detalle-id"></span>
                </div>
                <div class="detalle-item">
                    <label>Nombre:</label>
                    <span id="detalle-nombre"></span>
                </div>
                <div class="detalle-item">
                    <label>Descripción:</label>
                    <span id="detalle-descripcion"></span>
                </div>
                <div class="detalle-item">
                    <label>Costo:</label>
                    <span id="detalle-costo"></span>
                </div>
                <div class="detalle-item">
                    <label>Precio de Venta:</label>
                    <span id="detalle-precio"></span>
                </div>
                <div class="detalle-item">
                    <label>Margen de Ganancia:</label>
                    <span id="detalle-margen"></span>
                </div>
                <div class="detalle-item">
                    <label>Categoría:</label>
                    <span id="detalle-categoria"></span>
                </div>
                <div class="detalle-item">
                    <label>Fecha de Creación:</label>
                    <span id="detalle-fecha"></span>
                </div>
                <div class="detalle-item">
                    <label>Imágenes:</label>
                    <div id="detalle-imagenes" class="imagenes-grid"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cerrar-detalles">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal" id="modal-confirmar-eliminar">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h3>Confirmar Eliminación</h3>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro de que deseas eliminar este producto?</p>
            <p class="text-warning">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelar-eliminar">Cancelar</button>
            <button type="button" class="btn btn-danger" id="confirmar-eliminar">Eliminar</button>
        </div>
    </div>
</div>

<style>
    /* Estilos para el modal de detalles */
.detalles-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.detalle-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.detalle-item:last-child {
    border-bottom: none;
}

.detalle-item label {
    font-weight: 600;
    color: #333;
    min-width: 140px;
}

.detalle-item span {
    text-align: right;
    flex: 1;
    color: #666;
}

.imagenes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: 8px;
    align-items: start;
}

.imagenes-grid img {
    width: 100%;
    height: 90px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #eee;
}

/* Fullscreen viewer */
.fs-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.85); display: none; align-items: center; justify-content: center; z-index: 9999; }
.fs-backdrop img { max-width: 92vw; max-height: 92vh; border-radius: 6px; box-shadow: 0 10px 40px rgba(0,0,0,.5); }
.fs-backdrop.active { display: flex; }

/* Dropzone styles */
.dropzone-wrapper { border: 1px dashed #bbb; border-radius: 8px; padding: 14px; background: #fafafa; margin-top: 4px; }
.dropzone { cursor: pointer; padding: 12px; border-radius: 8px; text-align: center; color: #666; }
.dropzone.dragover { background: #f0f8ff; border-color: #5ab4f8; }
.dz-instructions { font-size: 13px; margin-bottom: 10px; }
.dz-hint { font-size: 12px; color: #999; margin-top: 4px; }
.dz-preview, .dz-existentes { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; }
.dz-item { position: relative; border: 1px solid #eee; border-radius: 6px; overflow: hidden; }
.dz-item img { display: block; width: 100%; height: 110px; object-fit: cover; }
.dz-actions { position: absolute; inset: auto 0 0 0; background: linear-gradient(transparent, rgba(0,0,0,.6)); color: #fff; display: flex; justify-content: space-between; align-items: center; padding: 6px; }
.dz-actions .dz-principal { font-size: 12px; display: flex; align-items: center; gap: 6px; }
.dz-actions .dz-remove { background: #ff5b5b; border: none; color: #fff; border-radius: 4px; padding: 4px 8px; cursor: pointer; }

/* Estilos para los botones de acción */
.btn-sm {
    margin: 0 2px;
}

.btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
}
</style>

<div class="fs-backdrop" id="fs-backdrop"><img id="fs-image" src="" alt=""></div>

<script>window.RUTA_IMG = '<?php echo RUTA_IMG ?>';</script>
<script src="<?php echo RUTA_JS ?>productos.js"></script>
<?php
include_once 'plantillas/html_cierre.inc.php';
?>