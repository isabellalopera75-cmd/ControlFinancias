@extends('layouts.app')

@section('title', 'Editar Movimiento')

@section('content')

<div class="max-w-lg mx-auto bg-white rounded-xl shadow p-6">
    <h2 class="text-xl font-bold text-gray-700 mb-6">Editar Movimiento</h2>

    @if($errors->any())
        <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="/movimiento/{{ $movimiento->id }}" method="POST">
        @csrf
        @method('PUT')

        <label class="text-sm text-gray-600">Descripción:</label>
        <input type="text" name="descripcion" value="{{ old('descripcion', $movimiento->descripcion) }}"
            class="w-full border border-gray-300 rounded px-3 py-2 mt-1 mb-4">

        <label class="text-sm text-gray-600">Monto:</label>
        <input type="number" name="monto" step="0.01" value="{{ old('monto', $movimiento->monto) }}"
            class="w-full border border-gray-300 rounded px-3 py-2 mt-1 mb-6">

        <div class="flex gap-4">
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Guardar cambios
            </button>
            <a href="/dashboard"
                class="w-full text-center bg-gray-200 text-gray-700 py-2 rounded hover:bg-gray-300">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection