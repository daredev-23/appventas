<?php
$titulo  = 'Ventas';
$scripts = ['venta.js', 'venta_nueva.js'];
require __DIR__ . '/../paginas/includes/cabecera.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><i class="fa-solid fa-cash-register me-2"></i>Ventas</h1>
  <button type="button" class="btn btn-primary" id="btnNueva"><i class="fa-solid fa-plus me-1"></i>Nueva venta</button>
</div>

<div id="aviso"></div>

<div class="input-group mb-3">
  <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
  <input type="search" class="form-control" id="buscar" maxlength="60" autocomplete="off"
         placeholder="Buscar por cliente, fecha (2026-10-17) o número de venta">
</div>

<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead>
      <tr>
        <th style="width: 6rem">N°</th>
        <th style="width: 11rem">Fecha</th>
        <th>Cliente</th>
        <th class="text-end" style="width: 10rem">Total</th>
        <th class="text-end" style="width: 9rem">Acciones</th>
      </tr>
    </thead>
    <tbody id="tabla"></tbody>
  </table>
</div>

<!-- Ventana del detalle de una venta -->
<div class="modal fade" id="modalVer" tabindex="-1" aria-labelledby="tituloVer" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title h5" id="tituloVer">Detalle de la venta</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="cargandoVer" class="text-body-secondary">Cargando...</div>
        <div id="errorVer" class="text-danger"></div>

        <div id="contenidoVer" class="d-none">
          <dl class="row mb-3">
            <dt class="col-sm-3">Fecha</dt>
            <dd class="col-sm-9" id="verFecha"></dd>
            <dt class="col-sm-3">Cliente</dt>
            <dd class="col-sm-9" id="verCliente"></dd>
            <dt class="col-sm-3">Registró</dt>
            <dd class="col-sm-9" id="verUsuario"></dd>
          </dl>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th class="text-end">Cant.</th>
                  <th class="text-end">Precio</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody id="verLineas"></tbody>
              <tfoot>
                <tr>
                  <th colspan="3" class="text-end">Total</th>
                  <th class="text-end" id="verTotal"></th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Ventana de confirmación para eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="tituloEliminar" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title h5" id="tituloEliminar">Eliminar venta</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        ¿Seguro que quieres eliminar la venta N° <strong id="numeroEliminar"></strong>?
        Se devolverá al stock lo que se vendió. Esta acción no se puede deshacer.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmarEliminar">
          <i class="fa-solid fa-trash me-1"></i>Eliminar
        </button>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/nueva.php'; ?>
<?php require __DIR__ . '/../paginas/includes/pie.php'; ?>
