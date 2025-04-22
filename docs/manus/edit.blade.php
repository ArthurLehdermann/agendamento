@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Editar Serviço</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('services.update', $service->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome do Serviço</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $service->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $service->description) }}</textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="duration" class="form-label">Duração (minutos)</label>
                                <input type="number" class="form-control" id="duration" name="duration" value="{{ old('duration', $service->duration) }}" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label for="price" class="form-label">Preço (R$)</label>
                                <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $service->price) }}" min="0" step="0.01" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="color" class="form-label">Cor (para exibição no calendário)</label>
                            <input type="color" class="form-control form-control-color" id="color" name="color" value="{{ old('color', $service->color ?? '#3490dc') }}">
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Ativo</label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('services.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Atualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
