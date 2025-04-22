@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Agendamentos</span>
                    <div>
                        <a href="{{ route('appointments.calendar') }}" class="btn btn-info btn-sm me-2">Calendário</a>
                        <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm">Novo Agendamento</a>
                    </div>
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

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th>Recurso</th>
                                    <th>Data/Hora</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($appointments as $appointment)
                                    <tr>
                                        <td>{{ $appointment->customer_name }}</td>
                                        <td>{{ $appointment->service->name }}</td>
                                        <td>{{ $appointment->resource->name }}</td>
                                        <td>{{ $appointment->start_time->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if ($appointment->status == 'scheduled')
                                                <span class="badge bg-primary">Agendado</span>
                                            @elseif ($appointment->status == 'confirmed')
                                                <span class="badge bg-success">Confirmado</span>
                                            @elseif ($appointment->status == 'in_progress')
                                                <span class="badge bg-warning">Em Andamento</span>
                                            @elseif ($appointment->status == 'completed')
                                                <span class="badge bg-info">Concluído</span>
                                            @elseif ($appointment->status == 'canceled')
                                                <span class="badge bg-danger">Cancelado</span>
                                            @elseif ($appointment->status == 'no_show')
                                                <span class="badge bg-secondary">Não Compareceu</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('appointments.show', $appointment->id) }}" class="btn btn-info btn-sm">Ver</a>
                                                <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                                <form action="{{ route('appointments.destroy', $appointment->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este agendamento?')">Excluir</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhum agendamento encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $appointments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
