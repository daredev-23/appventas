<?php
$titulo  = 'Provincias';
$scripts = ['provincia.js'];
require __DIR__ . '/../paginas/includes/cabecera.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><i class="fa-solid fa-map-location-dot me-2"></i>Provincias</h1>
  <button type="button" class="btn btn-primary" id="btnNuevo">
    <i class="fa-solid fa-plus me-1"></i>Nuevo
  </button>
</div>

<div id="aviso"></div>

<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead>
      <tr>
        <th style="width: 8rem">Código</th>
        <th>Provincia</th>
        <th>Departamento</th>
        <th class="text-end" style="width: 10rem">Acciones</th>
      </tr>
    </thead>
    <tbody id="tabla"></tbody>
  </table>
</div>

<!-- Ventana del formulario: sirve para crear y para editar -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="tituloForm" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="formProv" novalidate>
      <div class="modal-header">
        <h2 class="modal-title h5" id="tituloForm">Nueva provincia</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="modo" id="modo" value="crear">

        <div class="mb-3">
          <label for="departamento" class="form-label">Departamento</label>
          <select class="form-select" id="departamento" name="departamento">
            <option value="">Cargando...</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="codigo" class="form-label">Código</label>
          <input type="text" class="form-control" id="codigo" name="codigo"
                 maxlength="4" inputmode="numeric" autocomplete="off">
          <div class="form-text">4 dígitos: los 2 primeros son los del departamento (por ejemplo, 1599).</div>
        </div>

        <div class="mb-3">
          <label for="nombre" class="form-label">Provincia</label>
          <input type="text" class="form-control" id="nombre" name="nombre"
                 maxlength="40" autocomplete="off">
        </div>

        <div id="errorForm" class="text-danger small"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="btnGuardar">
          <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Ventana de confirmación para eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="tituloEliminar" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title h5" id="tituloEliminar">Eliminar provincia</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        ¿Seguro que quieres eliminar <strong id="nombreEliminar"></strong>?
        Esta acción no se puede deshacer.
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
<?php require __DIR__ . '/../paginas/includes/pie.php'; ?>
