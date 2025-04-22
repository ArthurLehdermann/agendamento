@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Editar Recurso</div>

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

                    <form method="POST" action="{{ route('resources.update', $resource->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome do Recurso</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $resource->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Tipo</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="bay" {{ old('type', $resource->type) == 'bay' ? 'selected' : '' }}>Baia</option>
                                <option value="professional" {{ old('type', $resource->type) == 'professional' ? 'selected' : '' }}>Profissional</option>
                                <option value="equipment" {{ old('type', $resource->type) == 'equipment' ? 'selected' : '' }}>Equipamento</option>
                                <option value="other" {{ old('type', $resource->type) == 'other' ? 'selected' : '' }}>Outro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $resource->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="color" class="form-label">Cor (para exibição no calendário)</label>
                            <input type="color" class="form-control form-control-color" id="color" name="color" value="{{ old('color', $resource->color ?? '#3490dc') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Horários de Trabalho</label>
                            <div class="card p-3">
                                @php
                                    $workingHours = old('working_hours', $resource->working_hours ?? []);
                                    if (is_string($workingHours)) {
                                        $workingHours = json_decode($workingHours, true) ?? [];
                                    }
                                @endphp
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[monday][enabled]" 
                                                    {{ isset($workingHours['monday']['enabled']) && $workingHours['monday']['enabled'] ? 'checked' : '' }}>
                                                Segunda-feira
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[monday][start]" 
                                                    value="{{ $workingHours['monday']['start'] ?? '08:00' }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[monday][end]" 
                                                    value="{{ $workingHours['monday']['end'] ?? '18:00' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[tuesday][enabled]" 
                                                    {{ isset($workingHours['tuesday']['enabled']) && $workingHours['tuesday']['enabled'] ? 'checked' : '' }}>
                                                Terça-feira
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[tuesday][start]" 
                                                    value="{{ $workingHours['tuesday']['start'] ?? '08:00' }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[tuesday][end]" 
                                                    value="{{ $workingHours['tuesday']['end'] ?? '18:00' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Repetir para os outros dias da semana -->
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[wednesday][enabled]" 
                                                    {{ isset($workingHours['wednesday']['enabled']) && $workingHours['wednesday']['enabled'] ? 'checked' : '' }}>
                                                Quarta-feira
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[wednesday][start]" 
                                                    value="{{ $workingHours['wednesday']['start'] ?? '08:00' }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[wednesday][end]" 
                                                    value="{{ $workingHours['wednesday']['end'] ?? '18:00' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[thursday][enabled]" 
                                                    {{ isset($workingHours['thursday']['enabled']) && $workingHours['thursday']['enabled'] ? 'checked' : '' }}>
                                                Quinta-feira
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[thursday][start]" 
                                                    value="{{ $workingHours['thursday']['start'] ?? '08:00' }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[thursday][end]" 
                                                    value="{{ $workingHours['thursday']['end'] ?? '18:00' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[friday][enabled]" 
                                                    {{ isset($workingHours['friday']['enabled']) && $workingHours['friday']['enabled'] ? 'checked' : '' }}>
                                                Sexta-feira
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[friday][start]" 
                                                    value="{{ $workingHours['friday']['start'] ?? '08:00' }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[friday][end]" 
                                                    value="{{ $workingHours['friday']['end'] ?? '18:00' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[saturday][enabled]" 
                                                    {{ isset($workingHours['saturday']['enabled']) && $workingHours['saturday']['enabled'] ? 'checked' : '' }}>
                                                Sábado
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[saturday][start]" 
                                                    value="{{ $workingHours['saturday']['start'] ?? '08:00' }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[saturday][end]" 
                                                    value="{{ $workingHours['saturday']['end'] ?? '12:00' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[sunday][enabled]" 
                                                    {{ isset($workingHours['sunday']['enabled']) && $workingHours['sunday']['enabled'] ? 'checked' : '' }}>
                                                Domingo
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[sunday][start]" 
                                                    value="{{ $workingHours['sunday']['start'] ?? '08:00' }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[sunday][end]" 
                                                    value="{{ $workingHours['sunday']['end'] ?? '12:00' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" {{ old('is_active', $resource->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Ativo</label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('resources.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Atualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
