<?php
$titulo  = 'Distritos';
$scripts = ['distrito.js'];
require __DIR__ . '/../paginas/includes/cabecera.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><i class="fa-solid fa-location-dot me-2"></i>Distritos</h1>
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
        <th>Distrito</th>
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
    <form class="modal-content" id="formDist" novalidate>
      <div class="modal-header">
        <h2 class="modal-title h5" id="tituloForm">Nuevo distrito</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="modo" id="modo" value="crear">

        <div class="mb-3">
          <label for="departamento" class="form-label">Departamento</label>
          <select class="form-select" id="departamento">
            <option value="">Cargando...</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="provincia" class="form-label">Provincia</label>
          <select class="form-select" id="provincia" name="provincia" disabled>
            <option value="">Primero elige un departamento</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="codigo" class="form-label">Código</label>
          <input type="text" class="form-control" id="codigo" name="codigo"
                 maxlength="6" inputmode="numeric" autocomplete="off">
          <div class="form-text">6 dígitos: los 4 primeros son los de la provincia (por ejemplo, 150199).</div>
        </div>

        <div class="mb-3">
          <label for="nombre" class="form-label">Distrito</label>
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
        <h2 class="modal-title h5" id="tituloEliminar">Eliminar distrito</h2>
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
