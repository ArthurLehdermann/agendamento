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
    .service-card {
        border-left: 4px solid #38c172;
    }
    .chart-container {
        height: 300px;
        margin-bottom: 2rem;
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
                    <h4 class="mb-0">Gestão de Serviços - {{ $tenant->name }}</h4>
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
                                    <h5 class="card-title">Total de Serviços</h5>
                                    <h2 class="display-4">{{ count($services) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Serviços Ativos</h5>
                                    <h2 class="display-4">{{ $services->where('is_active', true)->count() }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-info text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Receita Total</h5>
                                    @php
                                        $totalRevenue = 0;
                                        foreach ($serviceStats as $stats) {
                                            $totalRevenue += $stats['revenue'];
                                        }
                                    @endphp
                                    <h2 class="display-4">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Serviços Mais Populares</h5>
                                </div>
                                <div class="card-body">
                                    <div id="popularityChart" class="chart-container"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Serviços por Receita</h5>
                                </div>
                                <div class="card-body">
                                    <div id="revenueChart" class="chart-container"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Serviços -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Serviços e Desempenho</h5>
                                    <a href="{{ route('services.create') }}" class="btn btn-sm btn-primary">Adicionar Serviço</a>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @forelse ($services as $service)
                                            <div class="col-md-6 mb-3">
                                                <div class="card service-card">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h5 class="card-title mb-0">{{ $service->name }}</h5>
                                                            <span class="badge {{ $service->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                                {{ $service->is_active ? 'Ativo' : 'Inativo' }}
                                                            </span>
                                                        </div>
                                                        
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted">Duração: {{ $service->duration }} min</span>
                                                            <span class="fw-bold">R$ {{ number_format($service->price, 2, ',', '.') }}</span>
                                                        </div>
                                                        
                                                        <p class="card-text">{{ Str::limit($service->description, 100) }}</p>
                                                        
                                                        <div class="row mb-3">
                                                            <div class="col-4">
                                                                <div class="text-center">
                                                                    <div class="fw-bold">{{ $serviceStats[$service->id]['total'] ?? 0 }}</div>
                                                                    <div class="text-muted">Agendamentos</div>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="text-center">
                                                                    <div class="fw-bold">{{ $serviceStats[$service->id]['completed'] ?? 0 }}</div>
                                                                    <div class="text-muted">Concluídos</div>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="text-center">
                                                                    <div class="fw-bold">R$ {{ number_format($serviceStats[$service->id]['revenue'] ?? 0, 2, ',', '.') }}</div>
                                                                    <div class="text-muted">Receita</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="d-flex justify-content-between mt-3">
                                                            <a href="{{ route('services.show', $service->id) }}" class="btn btn-sm btn-info">Detalhes</a>
                                                            <a href="{{ route('services.edit', $service->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                                            <a href="{{ route('appointments.create', ['service_id' => $service->id]) }}" class="btn btn-sm btn-primary">Agendar</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <div class="alert alert-info">
                                                    Nenhum serviço cadastrado. <a href="{{ route('services.create') }}">Adicionar um serviço</a>.
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

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dados para os gráficos
        const services = @json($services);
        const serviceStats = @json($serviceStats);
        
        // Preparar dados para gráficos
        const popularityData = [];
        const revenueData = [];
        
        services.forEach(service => {
            const stats = serviceStats[service.id] || { total: 0, revenue: 0 };
            
            if (stats.total > 0) {
                popularityData.push({
                    service: service.name,
                    count: stats.total,
                    color: '#3490dc'
                });
            }
            
            if (stats.revenue > 0) {
                revenueData.push({
                    service: service.name,
                    revenue: stats.revenue,
                    color: '#38c172'
                });
            }
        });
        
        // Ordenar por popularidade e receita
        popularityData.sort((a, b) => b.count - a.count);
        revenueData.sort((a, b) => b.revenue - a.revenue);
        
        // Limitar a 5 itens
        const topPopularity = popularityData.slice(0, 5);
        const topRevenue = revenueData.slice(0, 5);
        
        // Gráfico de Popularidade
        if (topPopularity.length > 0) {
            const popularityCtx = document.getElementById('popularityChart').getContext('2d');
            new Chart(popularityCtx, {
                type: 'bar',
                data: {
                    labels: topPopularity.map(item => item.service),
                    datasets: [{
                        label: 'Agendamentos',
                        data: topPopularity.map(item => item.count),
                        backgroundColor: topPopularity.map(item => item.color),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
        
        // Gráfico de Receita
        if (topRevenue.length > 0) {
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: topRevenue.map(item => item.service),
                    datasets: [{
                        label: 'Receita (R$)',
                        data: topRevenue.map(item => item.revenue),
                        backgroundColor: topRevenue.map(item => item.color),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
