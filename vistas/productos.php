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
<div class="modal fade" id="modal-producto" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalProductoLabel">Agregar Producto</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-producto">
                <div class="modal-body">
                    <input type="hidden" id="producto-id">
                    <!-- Campos ocultos para gestión de imágenes -->
                    <input type="hidden" id="imagenes_eliminar" name="imagenes_eliminar">
                    <input type="hidden" id="imagen_principal_existente" name="imagen_principal_existente">

                    <div class="form-group mb-3">
                        <label for="producto-nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="producto-nombre" name="nombre" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="producto-descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="producto-descripcion" name="descripcion" rows="3"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="producto-costo" class="form-label">Costo *</label>
                                <input type="number" class="form-control" id="producto-costo" name="costo" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="producto-precio" class="form-label">Precio Venta *</label>
                                <input type="number" class="form-control" id="producto-precio" name="precio_venta" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="producto-categoria" class="form-label">Categoría *</label>
                        <select class="form-select" id="producto-categoria" name="categoria_id" required>
                            <option value="">Seleccionar categoría...</option>
                            <!-- Las categorías se cargarán via AJAX -->
                        </select>
                    </div>

                    <!-- Sección de imágenes principal -->
                    <div class="form-group mb-3">
                        <label class="form-label">Imagen principal *</label>
                        <div id="dropzone-principal-wrapper" class="dropzone-wrapper">
                            <div class="dropzone" id="dropzone-principal">
                                <div class="dz-instructions">
                                    Arrastra la imagen principal aquí o haz clic para seleccionar.
                                    <div class="dz-hint">Obligatoria. Reemplaza la anterior si eliges una nueva.</div>
                                </div>
                                <div class="dz-preview" id="dz-preview-principal"></div>
                            </div>
                        </div>
                        <input type="file" id="producto-imagen-principal" name="imagen_principal_file" accept="image/*" style="display: none;">
                        <div class="imagenes-existente" id="imagenes-principal-existente"></div>
                    </div>

                    <!-- Sección de imágenes adicionales -->
                    <div class="form-group mb-3">
                        <label class="form-label">Imágenes adicionales</label>
                        <div id="dropzone-extras-wrapper" class="dropzone-wrapper">
                            <div class="dropzone" id="dropzone-extras">
                                <div class="dz-instructions">
                                    Arrastra y suelta imágenes adicionales aquí o haz clic para seleccionar.
                                    <div class="dz-hint">JPG, PNG, WEBP, GIF. Máx 5MB c/u.</div>
                                </div>
                                <div class="dz-preview" id="dz-preview-extras"></div>
                            </div>
                        </div>
                        <input type="file" id="producto-imagenes" name="imagenes[]" accept="image/*" multiple style="display: none;">
                        <div class="imagenes-existente" id="imagenes-extras-existente"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="guardar-producto">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de detalles del producto -->
<div class="modal fade" id="modal-detalles-producto" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalDetallesLabel">Detalles del Producto</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <div class="detalle-item imagenes-item">
                        <label>Imágenes del Producto:</label>
                        <div id="detalle-imagenes" class="imagenes-grid"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="editar-desde-detalles">
                    <i class="fas fa-edit"></i> Editar Producto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="modal-confirmar-eliminar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalEliminarLabel">Confirmar Eliminación</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este producto?</p>
                <p class="text-warning">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmar-eliminar">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Gallery -->
<div class="fs-backdrop" id="fs-backdrop">
    <div class="fs-gallery-container">
        <button class="fs-nav fs-prev" id="fs-prev">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <div class="fs-image-container">
            <img id="fs-image" src="" alt="Vista ampliada">
            <div class="fs-image-info">
                <span id="fs-image-counter"></span>
                <span id="fs-image-principal-badge" class="principal-badge">
                    <i class="fas fa-star"></i> Principal
                </span>
            </div>
        </div>
        
        <button class="fs-nav fs-next" id="fs-next">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <button class="fs-close" id="fs-close">
            <i class="fas fa-times"></i>
        </button>
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

    .imagenes-item {
        align-items: stretch;
        flex-direction: column;
    }
    
    .imagenes-item label {
        align-self: flex-start;
        margin-bottom: 12px;
    }
    
    .imagenes-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
    }

    .imagen-card {
        position: relative;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        background: #fafafa;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .imagen-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .imagen-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .imagen-card:hover img {
        transform: scale(1.05);
    }

    .imagen-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: linear-gradient(135deg, #3AC47D, #2fa864);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .imagen-info {
        padding: 8px;
        text-align: center;
        background: white;
        border-top: 1px solid #f0f0f0;
    }

    .imagen-nombre {
        font-size: 11px;
        color: #666;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Estados especiales para la imagen principal */
    .imagen-card.principal {
        border: 2px solid #3AC47D;
        background: linear-gradient(135deg, #f8fff9, #f0f9f3);
    }

    .imagen-card.principal .imagen-badge {
        background: linear-gradient(135deg, #3AC47D, #2a9c5f);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .imagenes-grid {
            grid-template-columns: 1fr;
            gap: 12px;
            max-width: 400px;
        }
        
        .imagen-card img {
            height: 160px;
        }
    }

    @media (max-width: 480px) {
        .detalle-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        
        .detalle-item label {
            min-width: auto;
        }
        
        .detalle-item span {
            text-align: left;
        }
        
        .imagen-card img {
            height: 140px;
        }
    }

    /* Estado cuando no hay imágenes */
    .sin-imagenes {
        text-align: center;
        padding: 40px 20px;
        color: #888;
        background: #f9f9f9;
        border-radius: 8px;
        border: 2px dashed #ddd;
        grid-column: 1 / -1;
    }

    .sin-imagenes i {
        font-size: 48px;
        margin-bottom: 12px;
        color: #ccc;
    }

    .sin-imagenes p {
        margin: 0;
        font-size: 14px;
    }

    /* Fullscreen Gallery */
    .fs-backdrop { 
        position: fixed; 
        inset: 0; 
        background: rgba(0,0,0,0.95); 
        display: none; 
        align-items: center; 
        justify-content: center; 
        z-index: 10000;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .fs-backdrop.active { 
        display: flex; 
        opacity: 1;
    }

    .fs-gallery-container {
        position: relative;
        width: 90vw;
        height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fs-image-container {
        position: relative;
        max-width: 85%;
        max-height: 85%;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .fs-image-container img {
        max-width: 100%;
        max-height: calc(100% - 50px);
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }

    .fs-image-info {
        margin-top: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        color: white;
        font-size: 14px;
    }

    .fs-image-counter {
        font-weight: 600;
    }

    .principal-badge {
        background: linear-gradient(135deg, #3AC47D, #2a9c5f);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .fs-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 18px;
        backdrop-filter: blur(10px);
    }

    .fs-nav:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-50%) scale(1.1);
    }

    .fs-prev {
        left: 20px;
    }

    .fs-next {
        right: 20px;
    }

    .fs-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 16px;
        backdrop-filter: blur(10px);
    }

    .fs-close:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.1);
    }

    /* Dropzone styles */
    .dropzone-wrapper { 
        border: 2px dashed #ddd; 
        border-radius: 8px; 
        padding: 20px; 
        background: #fafafa; 
        margin-top: 8px;
        transition: all 0.3s ease;
    }
    
    .dropzone-wrapper.dragover { 
        border-color: #5ab4f8; 
        background: #f0f8ff; 
    }
    
    .dropzone { 
        cursor: pointer; 
        padding: 12px; 
        border-radius: 8px; 
        text-align: center; 
        color: #666; 
    }
    
    .dz-instructions { 
        font-size: 14px; 
        margin-bottom: 10px; 
    }
    
    .dz-hint { 
        font-size: 12px; 
        color: #999; 
        margin-top: 4px; 
    }
    
    .dz-preview, .dz-existentes { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); 
        gap: 12px; 
        margin-top: 15px;
    }
    
    .dz-item { 
        position: relative; 
        border: 1px solid #eee; 
        border-radius: 6px; 
        overflow: hidden; 
        background: white;
    }
    
    .dz-item img { 
        display: block; 
        width: 100%; 
        height: 120px; 
        object-fit: cover; 
    }
    
    .dz-actions { 
        position: absolute; 
        bottom: 0; 
        left: 0; 
        right: 0; 
        background: linear-gradient(transparent, rgba(0,0,0,.6)); 
        color: #fff; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        padding: 8px; 
        transform: translateY(100%);
        transition: transform 0.3s ease;
    }
    
    .dz-item:hover .dz-actions {
        transform: translateY(0);
    }
    
    .dz-actions .dz-principal { 
        font-size: 12px; 
        display: flex; 
        align-items: center; 
        gap: 6px; 
    }
    
    .dz-actions .dz-remove { 
        background: #ff5b5b; 
        border: none; 
        color: #fff; 
        border-radius: 4px; 
        padding: 4px 8px; 
        cursor: pointer; 
        font-size: 11px;
    }

    .dz-actions .dz-remove:hover {
        background: #ff4444;
    }

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

    /* Botón editar en detalles */
    #editar-desde-detalles {
        display: flex;
        align-items: center;
        gap: 6px;
    }
</style>

<script>window.RUTA_IMG = '<?php echo RUTA_IMG ?>';</script>
<script src="<?php echo RUTA_JS ?>productos.js"></script>
<?php
include_once 'plantillas/html_cierre.inc.php';
?>