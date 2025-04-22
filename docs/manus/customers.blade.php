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
    .customer-card {
        border-left: 4px solid #6c757d;
    }
    .chart-container {
        height: 300px;
        margin-bottom: 2rem;
    }
    .customer-table th, .customer-table td {
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Gestão de Clientes - {{ $tenant->name }}</h4>
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
                                    <h5 class="card-title">Total de Clientes</h5>
                                    <h2 class="display-4">{{ count($customers) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Clientes Ativos</h5>
                                    @php
                                        $activeCustomers = 0;
                                        foreach ($customerStats as $stats) {
                                            if ($stats['last_appointment'] && \Carbon\Carbon::parse($stats['last_appointment'])->greaterThan(\Carbon\Carbon::now()->subMonths(3))) {
                                                $activeCustomers++;
                                            }
                                        }
                                    @endphp
                                    <h2 class="display-4">{{ $activeCustomers }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-info text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Receita Total</h5>
                                    @php
                                        $totalRevenue = 0;
                                        foreach ($customerStats as $stats) {
                                            $totalRevenue += $stats['total_spent'];
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
                                    <h5 class="mb-0">Clientes Mais Frequentes</h5>
                                </div>
                                <div class="card-body">
                                    <div id="frequencyChart" class="chart-container"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Clientes por Valor Gasto</h5>
                                </div>
                                <div class="card-body">
                                    <div id="spendingChart" class="chart-container"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Clientes -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Lista de Clientes</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped customer-table">
                                            <thead>
                                                <tr>
                                                    <th>Nome</th>
                                                    <th>Contato</th>
                                                    <th>Agendamentos</th>
                                                    <th>Valor Gasto</th>
                                                    <th>Último Agendamento</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($customers as $customer)
                                                    <tr>
                                                        <td>{{ $customer->customer_name }}</td>
                                                        <td>
                                                            @if ($customerStats[$customer->customer_name]['email'])
                                                                <div>{{ $customerStats[$customer->customer_name]['email'] }}</div>
                                                            @endif
                                                            @if ($customerStats[$customer->customer_name]['phone'])
                                                                <div>{{ $customerStats[$customer->customer_name]['phone'] }}</div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div>Total: {{ $customerStats[$customer->customer_name]['total_appointments'] }}</div>
                                                            <div class="text-success">Concluídos: {{ $customerStats[$customer->customer_name]['completed_appointments'] }}</div>
                                                            <div class="text-danger">Cancelados: {{ $customerStats[$customer->customer_name]['canceled_appointments'] }}</div>
                                                        </td>
                                                        <td>R$ {{ number_format($customerStats[$customer->customer_name]['total_spent'], 2, ',', '.') }}</td>
                                                        <td>
                                                            @if ($customerStats[$customer->customer_name]['last_appointment'])
                                                                {{ \Carbon\Carbon::parse($customerStats[$customer->customer_name]['last_appointment'])->format('d/m/Y H:i') }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="{{ route('appointments.create', ['customer_name' => $customer->customer_name, 'customer_email' => $customerStats[$customer->customer_name]['email'], 'customer_phone' => $customerStats[$customer->customer_name]['phone']]) }}" class="btn btn-sm btn-primary">Agendar</a>
                                                                <a href="{{ route('appointments.index', ['customer' => $customer->customer_name]) }}" class="btn btn-sm btn-info">Histórico</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">Nenhum cliente encontrado.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
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
        const customerStats = @json($customerStats);
        
        // Preparar dados para gráficos
        const frequencyData = [];
        const spendingData = [];
        
        for (const [name, stats] of Object.entries(customerStats)) {
            if (stats.total_appointments > 0) {
                frequencyData.push({
                    name: name,
                    count: stats.total_appointments,
                    color: '#3490dc'
                });
            }
            
            if (stats.total_spent > 0) {
                spendingData.push({
                    name: name,
                    spent: stats.total_spent,
                    color: '#38c172'
                });
            }
        }
        
        // Ordenar por frequência e valor gasto
        frequencyData.sort((a, b) => b.count - a.count);
        spendingData.sort((a, b) => b.spent - a.spent);
        
        // Limitar a 5 itens
        const topFrequency = frequencyData.slice(0, 5);
        const topSpending = spendingData.slice(0, 5);
        
        // Gráfico de Frequência
        if (topFrequency.length > 0) {
            const frequencyCtx = document.getElementById('frequencyChart').getContext('2d');
            new Chart(frequencyCtx, {
                type: 'bar',
                data: {
                    labels: topFrequency.map(item => item.name),
                    datasets: [{
                        label: 'Agendamentos',
                        data: topFrequency.map(item => item.count),
                        backgroundColor: topFrequency.map(item => item.color),
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
        
        // Gráfico de Valor Gasto
        if (topSpending.length > 0) {
            const spendingCtx = document.getElementById('spendingChart').getContext('2d');
            new Chart(spendingCtx, {
                type: 'bar',
                data: {
                    labels: topSpending.map(item => item.name),
                    datasets: [{
                        label: 'Valor Gasto (R$)',
                        data: topSpending.map(item => item.spent),
                        backgroundColor: topSpending.map(item => item.color),
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
