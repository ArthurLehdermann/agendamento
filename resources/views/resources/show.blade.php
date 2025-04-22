@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Detalhes do Recurso</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Nome:</div>
                        <div class="col-md-9">{{ $resource->name }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Tipo:</div>
                        <div class="col-md-9">
                            @if ($resource->type == 'bay')
                                Baia
                            @elseif ($resource->type == 'professional')
                                Profissional
                            @elseif ($resource->type == 'equipment')
                                Equipamento
                            @else
                                {{ $resource->type }}
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Descrição:</div>
                        <div class="col-md-9">{{ $resource->description ?? 'Não informada' }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Cor:</div>
                        <div class="col-md-9">
                            <div style="width: 30px; height: 30px; background-color: {{ $resource->color ?? '#3490dc' }}; border-radius: 4px;"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Status:</div>
                        <div class="col-md-9">
                            @if ($resource->is_active)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-danger">Inativo</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Horários de Trabalho:</div>
                        <div class="col-md-9">
                            @php
                                $workingHours = $resource->working_hours;
                                if (is_string($workingHours)) {
                                    $workingHours = json_decode($workingHours, true) ?? [];
                                }
                                
                                $days = [
                                    'monday' => 'Segunda-feira',
                                    'tuesday' => 'Terça-feira',
                                    'wednesday' => 'Quarta-feira',
                                    'thursday' => 'Quinta-feira',
                                    'friday' => 'Sexta-feira',
                                    'saturday' => 'Sábado',
                                    'sunday' => 'Domingo'
                                ];
                            @endphp
                            
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Dia</th>
                                            <th>Disponível</th>
                                            <th>Horário</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($days as $key => $day)
                                            <tr>
                                                <td>{{ $day }}</td>
                                                <td>
                                                    @if(isset($workingHours[$key]['enabled']) && $workingHours[$key]['enabled'])
                                                        <span class="badge bg-success">Sim</span>
                                                    @else
                                                        <span class="badge bg-danger">Não</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($workingHours[$key]['enabled']) && $workingHours[$key]['enabled'])
                                                        {{ $workingHours[$key]['start'] ?? '08:00' }} - {{ $workingHours[$key]['end'] ?? '18:00' }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Data de Criação:</div>
                        <div class="col-md-9">{{ $resource->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Última Atualização:</div>
                        <div class="col-md-9">{{ $resource->updated_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('resources.index') }}" class="btn btn-secondary">Voltar</a>
                        <div>
                            <a href="{{ route('resources.edit', $resource->id) }}" class="btn btn-warning">Editar</a>
                            <form action="{{ route('resources.destroy', $resource->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este recurso?')">Excluir</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
