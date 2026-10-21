<!-- Ventana del formulario de nueva venta -->
<div class="modal fade" id="modalNueva" tabindex="-1" aria-labelledby="tituloNueva" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title h5" id="tituloNueva">Nueva venta</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="cargandoNueva" class="text-body-secondary">Cargando clientes y productos...</div>
        <div id="errorCarga" class="text-danger"></div>

        <div id="contenidoNueva" class="d-none">
          <div class="mb-3">
            <label for="cliente" class="form-label">Cliente</label>
            <select class="form-select" id="cliente"></select>
          </div>

          <div class="row g-2 align-items-end mb-3">
            <div class="col-md-7">
              <label for="producto" class="form-label">Producto</label>
              <select class="form-select" id="producto"></select>
            </div>
            <div class="col-6 col-md-2">
              <label for="cantidad" class="form-label">Cantidad</label>
              <input type="number" class="form-control" id="cantidad"
                     min="1" max="9999" value="1" inputmode="numeric">
            </div>
            <div class="col-6 col-md-3">
              <button type="button" class="btn btn-outline-primary w-100" id="btnAgregar">
                <i class="fa-solid fa-plus me-1"></i>Agregar
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th class="text-end">Cant.</th>
                  <th class="text-end">Precio</th>
                  <th class="text-end">Subtotal</th>
                  <th style="width: 3rem"></th>
                </tr>
              </thead>
              <tbody id="lineasNueva"></tbody>
              <tfoot>
                <tr>
                  <th colspan="3" class="text-end">Total</th>
                  <th class="text-end" id="totalNueva">S/ 0.00</th>
                  <th></th>
                </tr>
              </tfoot>
            </table>
          </div>

          <div id="errorNueva" class="text-danger small mt-2"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarVenta" disabled>
          <i class="fa-solid fa-floppy-disk me-1"></i>Guardar venta
        </button>
      </div>
    </div>
  </div>
</div>
