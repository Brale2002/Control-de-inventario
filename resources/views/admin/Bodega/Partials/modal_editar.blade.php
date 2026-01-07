<div class="modal fade" id="modalEditarProducto" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="formEditarProducto" method="POST">
          @csrf
          @method('PUT')
          <input type="hidden" id="edit_id" name="id">
          <div class="modal-header"><h5 class="modal-title">Editar producto</h5></div>
          <div class="modal-body">
            <div class="mb-2"><label>Código</label><input id="edit_codigo" name="codigo" class="form-control" required></div>
            <div class="mb-2"><label>Nombre</label><input id="edit_nombre" name="nombre" class="form-control" required></div>
            <div class="mb-2"><label>Descripción</label><textarea id="edit_descripcion" name="descripcion" class="form-control"></textarea></div>
            <div class="mb-2"><label>Categoria</label><input id="edit_categoria" name="categoria" class="form-control"></div>
            <div class="mb-2"><label>Precio</label><input id="edit_precio" name="precio" type="number" step="0.01" class="form-control" required></div>
            <div class="mb-2"><label>Iva (%)</label><input id="edit_iva" name="iva" type="number" step="0.01" class="form-control"></div>
            <div class="mb-2"><label>Stock</label><input id="edit_stock" name="stock" type="number" class="form-control"></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cerrar</button>
            <button class="btn btn-primary" type="submit">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <script>
  document.getElementById('formEditarProducto').addEventListener('submit', function(e){
      e.preventDefault();
      const id = document.getElementById('edit_id').value;
      const form = e.target;
      const data = new FormData(form);
      fetch(`/admin/bodega/producto/${id}`,{
          method:'POST', // fallback for some servers; we will send _method=PUT
          headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'},
          body: data
      }).then(r=> {
          // redirect to index to refresh
          window.location.reload();
      }).catch(()=> alert('Error'));
  });
  </script>
  