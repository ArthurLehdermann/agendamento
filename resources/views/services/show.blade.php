@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Detalhes do Serviço</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Nome:</div>
                        <div class="col-md-9">{{ $service->name }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Descrição:</div>
                        <div class="col-md-9">{{ $service->description ?? 'Não informada' }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Duração:</div>
                        <div class="col-md-9">{{ $service->duration }} minutos</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Preço:</div>
                        <div class="col-md-9">R$ {{ number_format($service->price, 2, ',', '.') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Cor:</div>
                        <div class="col-md-9">
                            <div style="width: 30px; height: 30px; background-color: {{ $service->color ?? '#3490dc' }}; border-radius: 4px;"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Status:</div>
                        <div class="col-md-9">
                            @if ($service->is_active)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-danger">Inativo</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Data de Criação:</div>
                        <div class="col-md-9">{{ $service->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Última Atualização:</div>
                        <div class="col-md-9">{{ $service->updated_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('services.index') }}" class="btn btn-secondary">Voltar</a>
                        <div>
                            <a href="{{ route('services.edit', $service->id) }}" class="btn btn-warning">Editar</a>
                            <form action="{{ route('services.destroy', $service->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este serviço?')">Excluir</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
