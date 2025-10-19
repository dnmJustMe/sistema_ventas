<?php
$titulo = 'notJustPrint - Ofertas';
$menu_activo = 'ofertas';

include_once 'plantillas/html_declaracion.inc.php';
?>

<div class="dashboard-body">
    <?php include_once 'plantillas/navbar_dashboard.inc.php'; ?>

    <div class="dashboard-container">
        <main class="dashboard-content">
            <div class="content-header">
                <h1>Gestión de Ofertas</h1>
                <p>Administra las ofertas del catálogo</p>
            </div>

            <!-- Barra de herramientas -->
            <div class="toolbar">
                <button class="btn btn-primary" id="btn-agregar-oferta">
                    <i class="fas fa-plus"></i> Agregar Oferta
                </button>
            </div>

            <!-- Tabla de ofertas -->
            <div class="table-container">
                <table class="table table-striped" id="tabla-ofertas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio Final</th>
                            <th>Productos</th>
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

<!-- Modal para agregar/editar oferta -->
<div class="modal fade" id="modal-oferta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalOfertaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalOfertaLabel">Agregar Oferta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-oferta">
                <div class="modal-body">
                    <input type="hidden" id="oferta-id">
                    <input type="hidden" id="productos_oferta" name="productos">

                    <!-- Sección 1: Datos básicos de la oferta -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="oferta-nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="oferta-nombre" name="nombre" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="oferta-descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="oferta-descripcion" name="descripcion" rows="3" placeholder="Describe los detalles de la oferta..."></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label for="oferta-precio" class="form-label">Precio Final *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="oferta-precio" name="precio_final_venta" step="0.01" min="0" placeholder="0.00" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <!-- Sección de imagen principal -->
                            <div class="form-group mb-3">
                                <label class="form-label">Imagen principal *</label>
                                <div id="dropzone-principal-wrapper" class="dropzone-wrapper">
                                    <div class="dropzone" id="dropzone-principal">
                                        <div class="dz-instructions">
                                            <i class="fas fa-cloud-upload-alt fa-2x mb-3 text-muted"></i>
                                            <div>Arrastra la imagen principal aquí</div>
                                            <div class="dz-hint">o haz clic para seleccionar</div>
                                            <div class="dz-requirements mt-2">
                                                <small class="text-muted">Formatos: JPG, PNG, WEBP, GIF | Máx: 5MB</small>
                                            </div>
                                        </div>
                                        <div class="dz-preview" id="dz-preview-principal"></div>
                                    </div>
                                </div>
                                <input type="file" id="oferta-imagen-principal" name="imagen_principal_file" accept="image/*" style="display: none;">
                                <div class="imagenes-existente" id="imagenes-principal-existente"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Línea separadora -->
                    <hr class="my-4">

                    <!-- Sección 2: Productos de la oferta -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">Productos de la Oferta</h5>
                            
                            <!-- Búsqueda de productos -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h6 class="card-title">Agregar Productos</h6>
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-8">
                                            <label for="buscar-producto" class="form-label">Buscar producto</label>
                                            <div class="search-box">
                                                <i class="fas fa-search"></i>
                                                <input type="text" id="buscar-producto" class="form-control" placeholder="Escribe el nombre del producto...">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-primary w-100" id="btn-buscar-producto">
                                                <i class="fas fa-search"></i> Buscar Productos
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Resultados de búsqueda -->
                                    <div id="resultados-busqueda" class="mt-3" style="display: none;">
                                        <h6 class="border-bottom pb-2">Resultados de búsqueda:</h6>
                                        <div id="lista-resultados" class="row g-2"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Productos agregados -->
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title d-flex justify-content-between align-items-center">
                                        <span>Productos en la oferta</span>
                                        <span class="badge bg-primary" id="contador-productos">0 productos</span>
                                    </h6>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="tabla-productos-oferta">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="40%">Producto</th>
                                                    <th width="20%">Precio Unitario</th>
                                                    <th width="15%">Cantidad</th>
                                                    <th width="15%">Subtotal</th>
                                                    <th width="10%">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="productos-agregados">
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                                        No hay productos agregados
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot id="footer-productos" style="display: none;">
                                                <tr class="table-active">
                                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                    <td><strong id="total-oferta">$0.00</strong></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="guardar-oferta">
                        <i class="fas fa-save"></i> Guardar Oferta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de detalles de la oferta -->
<div class="modal fade" id="modal-detalles-oferta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalDetallesLabel">Detalles de la Oferta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="detalles-container">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="detalle-item">
                                <label>ID:</label>
                                <span id="detalle-id" class="badge bg-secondary"></span>
                            </div>
                            <div class="detalle-item">
                                <label>Nombre:</label>
                                <span id="detalle-nombre" class="fw-bold"></span>
                            </div>
                            <div class="detalle-item">
                                <label>Descripción:</label>
                                <span id="detalle-descripcion"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detalle-item">
                                <label>Precio Final:</label>
                                <span id="detalle-precio" class="fw-bold text-success fs-5"></span>
                            </div>
                            <div class="detalle-item">
                                <label>Fecha de Creación:</label>
                                <span id="detalle-fecha"></span>
                            </div>
                        </div>
                    </div>

                    <div class="detalle-item imagenes-item mb-4">
                        <label class="fw-bold">Imagen Principal:</label>
                        <div id="detalle-imagen" class="imagen-principal-container mt-2"></div>
                    </div>

                    <div class="detalle-item productos-item">
                        <label class="fw-bold">Productos Incluidos:</label>
                        <div id="detalle-productos" class="productos-lista mt-3"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="editar-desde-detalles">
                    <i class="fas fa-edit"></i> Editar Oferta
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
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                <p class="mb-2">¿Estás seguro de que deseas eliminar esta oferta?</p>
                <p class="text-warning small mb-0">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmar-eliminar">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
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
                <span id="fs-image-title"></span>
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
    /* Estilos mejorados para el modal de ofertas */
    .productos-oferta-container {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        background: #f8f9fa;
    }

    .producto-resultado {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 10px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .producto-resultado:hover {
        background: #f8f9fa;
        border-color: #3AC47D;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }

    .producto-agregado {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        margin-bottom: 8px;
        background: white;
        transition: all 0.3s ease;
    }

    .producto-agregado:hover {
        background: #f8f9fa;
    }

    .producto-info {
        flex: 1;
    }

    .producto-nombre {
        font-weight: 600;
        margin-bottom: 4px;
        color: #333;
    }

    .producto-precio {
        color: #666;
        font-size: 0.9em;
    }

    .cantidad-control {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 15px;
    }

    .cantidad-control input {
        width: 80px;
        text-align: center;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 6px;
    }

    .imagen-principal-container {
        text-align: center;
    }

    .imagen-principal-container img {
        max-width: 300px;
        max-height: 200px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.3s ease;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .imagen-principal-container img:hover {
        transform: scale(1.05);
    }

    .productos-lista {
        max-height: 400px;
        overflow-y: auto;
    }

    .producto-detalle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 10px;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .producto-detalle:hover {
        background: #e9ecef;
    }

    .producto-detalle-info {
        flex: 1;
    }

    .producto-detalle-cantidad {
        background: #3AC47D;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9em;
    }

    .producto-imagen-mini {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        margin-right: 15px;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .producto-imagen-mini:hover {
        transform: scale(1.1);
    }

    /* Estilos para la tabla de productos en oferta */
    #tabla-productos-oferta {
        margin-bottom: 0;
        font-size: 0.9rem;
    }

    #tabla-productos-oferta tbody tr:hover {
        background-color: rgba(58, 196, 125, 0.05);
    }

    #tabla-productos-oferta th {
        border-top: none;
        font-weight: 600;
        color: #495057;
    }

    /* Dropzone mejorado */
    .dropzone-wrapper {
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 25px;
        background: #fafafa;
        margin-top: 8px;
        transition: all 0.3s ease;
    }
    
    .dropzone-wrapper.dragover {
        border-color: #3AC47D;
        background: #f0f9f4;
    }
    
    .dropzone {
        cursor: pointer;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        color: #6c757d;
        transition: all 0.3s ease;
    }
    
    .dz-instructions {
        font-size: 16px;
        margin-bottom: 10px;
        color: #495057;
    }
    
    .dz-hint {
        font-size: 14px;
        color: #6c757d;
        margin-top: 8px;
    }
    
    .dz-requirements {
        font-size: 12px;
    }
    
    .dz-preview {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .dz-item {
        position: relative;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        background: linear-gradient(transparent, rgba(0,0,0,.7));
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        transform: translateY(100%);
        transition: transform 0.3s ease;
    }
    
    .dz-item:hover .dz-actions {
        transform: translateY(0);
    }
    
    .dz-principal-badge {
        background: #3AC47D;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .dz-actions .dz-remove {
        background: #dc3545;
        border: none;
        color: #fff;
        border-radius: 4px;
        padding: 4px 8px;
        cursor: pointer;
        font-size: 11px;
        transition: background 0.3s ease;
    }

    .dz-actions .dz-remove:hover {
        background: #c82333;
    }

    /* Mejoras para detalles */
    .detalle-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
        flex-wrap: wrap;
    }

    .detalle-item:last-child {
        border-bottom: none;
    }

    .detalle-item label {
        font-weight: 600;
        color: #495057;
        min-width: 140px;
        margin-bottom: 4px;
    }

    .detalle-item span {
        text-align: right;
        flex: 1;
        color: #6c757d;
        min-width: 150px;
    }

    /* Fullscreen Gallery Styles */
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

    /* Responsive */
    @media (max-width: 768px) {
        .detalle-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .detalle-item span {
            text-align: left;
            margin-top: 4px;
        }
        
        .producto-resultado,
        .producto-agregado {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .cantidad-control {
            margin: 10px 0 0 0;
            align-self: stretch;
            justify-content: space-between;
        }

        .fs-gallery-container {
            width: 95vw;
            height: 95vh;
        }

        .fs-nav {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }

        .fs-prev {
            left: 10px;
        }

        .fs-next {
            right: 10px;
        }
    }
</style>

<script>window.RUTA_IMG = '<?php echo RUTA_IMG ?>';</script>
<script src="<?php echo RUTA_JS ?>ofertas.js"></script>

<?php
include_once 'plantillas/html_cierre.inc.php';
?>