@extends('layouts.dashboard')

@section('content')

<h2 class="mb-4">Administración de Usuarios</h2>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<a href="{{ route('usuarios.create') }}" class="btn btn-primary mb-3">Crear usuario</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($usuarios as $usuario)
            <tr>
                <td>{{ $usuario->nombre }}</td>
                <td>{{ $usuario->correo }}</td>
                <td>{{ $usuario->rol }}</td>
                <td>
                    <a href="#" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('admin.usuarios.destroy', $usuario->id) }}" 
                        method="POST" 
                        class="d-inline">
                  
                      @csrf
                      @method('DELETE')
                  
                      <button type="submit" class="btn btn-danger btn-sm"
                              onclick="return confirm('¿Seguro de eliminar este usuario?')">
                          Eliminar
                      </button>
                  </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
