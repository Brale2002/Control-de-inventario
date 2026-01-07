<div class="modal fade" id="modalEntrada" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEntrada">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Entrada de inventario</h5></div>
        <div class="modal-body">
          <input type="hidden" id="entrada_producto_id" name="producto_id">
          <p>Producto: <strong id="entrada_nombre"></strong></p>
          <div class="mb-2"><label>Cantidad</label><input id="entrada_cantidad" name="cantidad" type="number" class="form-control" value="1" min="1" required></div>
          <div class="mb-2"><label>Precio unitario (opcional)</label><input id="entrada_precio" name="precio_unitario" type="number" step="0.01" class="form-control"></div>
          <div class="mb-2"><label>Nota</label><input name="nota" type="text" class="form-control"></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cerrar</button>
          <button class="btn btn-success" type="submit">Registrar entrada</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('formEntrada').addEventListener('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this);
    fetch("{{ route('admin.bodega.entrada') }}", {
        method: 'POST',
        headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: fd
    }).then(r=>r.json()).then(json=>{
        if(json.success) window.location.reload();
        else alert('Error: '+JSON.stringify(json));
    }).catch(()=> alert('Error'));
});
</script>
