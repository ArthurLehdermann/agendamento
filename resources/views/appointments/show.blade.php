@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Detalhes do Agendamento</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Cliente:</div>
                        <div class="col-md-9">{{ $appointment->customer_name }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Contato:</div>
                        <div class="col-md-9">
                            @if ($appointment->customer_email)
                                <div>Email: {{ $appointment->customer_email }}</div>
                            @endif
                            @if ($appointment->customer_phone)
                                <div>Telefone: {{ $appointment->customer_phone }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Serviço:</div>
                        <div class="col-md-9">{{ $appointment->service->name }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Duração:</div>
                        <div class="col-md-9">{{ $appointment->service->duration }} minutos</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Preço:</div>
                        <div class="col-md-9">R$ {{ number_format($appointment->service->price, 2, ',', '.') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Recurso:</div>
                        <div class="col-md-9">
                            {{ $appointment->resource->name }}
                            ({{ $appointment->resource->type == 'bay' ? 'Baia' : ($appointment->resource->type == 'professional' ? 'Profissional' : $appointment->resource->type) }})
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Data e Hora:</div>
                        <div class="col-md-9">{{ $appointment->start_time->format('d/m/Y H:i') }} - {{ $appointment->end_time->format('H:i') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Status:</div>
                        <div class="col-md-9">
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
                        </div>
                    </div>

                    @if ($appointment->notes)
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Observações:</div>
                            <div class="col-md-9">{{ $appointment->notes }}</div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Agendado por:</div>
                        <div class="col-md-9">{{ $appointment->user->name }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Data de Criação:</div>
                        <div class="col-md-9">{{ $appointment->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Última Atualização:</div>
                        <div class="col-md-9">{{ $appointment->updated_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">Voltar</a>
                        <div>
                            <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn btn-warning">Editar</a>
                            <form action="{{ route('appointments.destroy', $appointment->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este agendamento?')">Excluir</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
