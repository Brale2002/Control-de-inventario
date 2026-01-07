<div class="modal fade" id="modalCrearProducto" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{ route('admin.bodega.store') }}" method="POST">
          @csrf
          <div class="modal-header"><h5 class="modal-title">Crear producto</h5></div>
          <div class="modal-body">
            <div class="mb-2"><label>Código</label><input name="codigo" class="form-control" required></div>
            <div class="mb-2"><label>Nombre</label><input name="nombre" class="form-control" required></div>
            <div class="mb-2"><label>Descripción</label><textarea name="descripcion" class="form-control"></textarea></div>
            <div class="mb-2"><label>Categoria</label><input name="categoria" class="form-control"></div>
            <div class="mb-2"><label>Precio</label><input name="precio" type="number" step="0.01" class="form-control" required></div>
            <div class="mb-2"><label>Iva (%)</label><input name="iva" type="number" step="0.01" class="form-control"></div>
            <div class="mb-2"><label>Stock inicial</label><input name="stock" type="number" class="form-control" value="0"></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cerrar</button>
            <button class="btn btn-primary" type="submit">Crear</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  