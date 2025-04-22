@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Criar Novo Recurso</div>

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

                    <form method="POST" action="{{ route('resources.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome do Recurso</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Tipo</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="bay" {{ old('type') == 'bay' ? 'selected' : '' }}>Baia</option>
                                <option value="professional" {{ old('type') == 'professional' ? 'selected' : '' }}>Profissional</option>
                                <option value="equipment" {{ old('type') == 'equipment' ? 'selected' : '' }}>Equipamento</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Outro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="color" class="form-label">Cor (para exibição no calendário)</label>
                            <input type="color" class="form-control form-control-color" id="color" name="color" value="{{ old('color', '#3490dc') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Horários de Trabalho</label>
                            <div class="card p-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[monday][enabled]" {{ old('working_hours.monday.enabled') ? 'checked' : '' }}>
                                                Segunda-feira
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[monday][start]" value="{{ old('working_hours.monday.start', '08:00') }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[monday][end]" value="{{ old('working_hours.monday.end', '18:00') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[tuesday][enabled]" {{ old('working_hours.tuesday.enabled') ? 'checked' : '' }}>
                                                Terça-feira
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[tuesday][start]" value="{{ old('working_hours.tuesday.start', '08:00') }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[tuesday][end]" value="{{ old('working_hours.tuesday.end', '18:00') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Repetir para os outros dias da semana -->
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[wednesday][enabled]" {{ old('working_hours.wednesday.enabled') ? 'checked' : '' }}>
                                                Quarta-feira
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[wednesday][start]" value="{{ old('working_hours.wednesday.start', '08:00') }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[wednesday][end]" value="{{ old('working_hours.wednesday.end', '18:00') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[thursday][enabled]" {{ old('working_hours.thursday.enabled') ? 'checked' : '' }}>
                                                Quinta-feira
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[thursday][start]" value="{{ old('working_hours.thursday.start', '08:00') }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[thursday][end]" value="{{ old('working_hours.thursday.end', '18:00') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[friday][enabled]" {{ old('working_hours.friday.enabled') ? 'checked' : '' }}>
                                                Sexta-feira
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[friday][start]" value="{{ old('working_hours.friday.start', '08:00') }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[friday][end]" value="{{ old('working_hours.friday.end', '18:00') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[saturday][enabled]" {{ old('working_hours.saturday.enabled') ? 'checked' : '' }}>
                                                Sábado
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[saturday][start]" value="{{ old('working_hours.saturday.start', '08:00') }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[saturday][end]" value="{{ old('working_hours.saturday.end', '12:00') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="working_hours[sunday][enabled]" {{ old('working_hours.sunday.enabled') ? 'checked' : '' }}>
                                                Domingo
                                            </label>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[sunday][start]" value="{{ old('working_hours.sunday.start', '08:00') }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control form-control-sm" name="working_hours[sunday][end]" value="{{ old('working_hours.sunday.end', '12:00') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" {{ old('is_active') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Ativo</label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('resources.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
