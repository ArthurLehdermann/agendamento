@extends('layouts.app')

@section('styles')
<style>
    .stats-card {
        transition: all 0.3s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .resource-card {
        border-left: 4px solid #3490dc;
    }
    .resource-stats {
        font-size: 0.9rem;
    }
    .progress {
        height: 10px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Gestão de Recursos - {{ $tenant->name }}</h4>
                    <a href="{{ route('dashboard.index') }}" class="btn btn-sm btn-secondary">Voltar ao Dashboard</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Estatísticas Rápidas -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total de Recursos</h5>
                                    <h2 class="display-4">{{ count($resources) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Recursos Ativos</h5>
                                    <h2 class="display-4">{{ $resources->where('is_active', true)->count() }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-info text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Taxa Média de Conclusão</h5>
                                    @php
                                        $totalRate = 0;
                                        $countResources = 0;
                                        foreach ($resourceStats as $stats) {
                                            if ($stats['total'] > 0) {
                                                $totalRate += $stats['completion_rate'];
                                                $countResources++;
                                            }
                                        }
                                        $averageRate = $countResources > 0 ? round($totalRate / $countResources) : 0;
                                    @endphp
                                    <h2 class="display-4">{{ $averageRate }}%</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Recursos -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Recursos e Desempenho</h5>
                                    <a href="{{ route('resources.create') }}" class="btn btn-sm btn-primary">Adicionar Recurso</a>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @forelse ($resources as $resource)
                                            <div class="col-md-6 mb-3">
                                                <div class="card resource-card">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h5 class="card-title mb-0">{{ $resource->name }}</h5>
                                                            <span class="badge {{ $resource->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                                {{ $resource->is_active ? 'Ativo' : 'Inativo' }}
                                                            </span>
                                                        </div>
                                                        
                                                        <p class="card-text text-muted">
                                                            {{ $resource->type == 'bay' ? 'Baia' : ($resource->type == 'professional' ? 'Profissional' : $resource->type) }}
                                                        </p>
                                                        
                                                        <div class="resource-stats mb-3">
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <div class="text-center">
                                                                        <div class="fw-bold">{{ $resourceStats[$resource->id]['total'] ?? 0 }}</div>
                                                                        <div class="text-muted">Total</div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div class="text-center">
                                                                        <div class="fw-bold">{{ $resourceStats[$resource->id]['completed'] ?? 0 }}</div>
                                                                        <div class="text-muted">Concluídos</div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div class="text-center">
                                                                        <div class="fw-bold">{{ $resourceStats[$resource->id]['canceled'] ?? 0 }}</div>
                                                                        <div class="text-muted">Cancelados</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-2">
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <span>Taxa de Conclusão</span>
                                                                <span>{{ $resourceStats[$resource->id]['completion_rate'] ?? 0 }}%</span>
                                                            </div>
                                                            <div class="progress">
                                                                <div class="progress-bar bg-success" role="progressbar" 
                                                                    style="width: {{ $resourceStats[$resource->id]['completion_rate'] ?? 0 }}%" 
                                                                    aria-valuenow="{{ $resourceStats[$resource->id]['completion_rate'] ?? 0 }}" 
                                                                    aria-valuemin="0" 
                                                                    aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="d-flex justify-content-between mt-3">
                                                            <a href="{{ route('resources.show', $resource->id) }}" class="btn btn-sm btn-info">Detalhes</a>
                                                            <a href="{{ route('resources.edit', $resource->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                                            <a href="{{ route('appointments.calendar', ['resource_id' => $resource->id]) }}" class="btn btn-sm btn-primary">Ver Agenda</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <div class="alert alert-info">
                                                    Nenhum recurso cadastrado. <a href="{{ route('resources.create') }}">Adicionar um recurso</a>.
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
