@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recursos</span>
                    <a href="{{ route('resources.create') }}" class="btn btn-primary btn-sm">Novo Recurso</a>
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
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($resources as $resource)
                                    <tr>
                                        <td>{{ $resource->name }}</td>
                                        <td>
                                            @if ($resource->type == 'bay')
                                                Baia
                                            @elseif ($resource->type == 'professional')
                                                Profissional
                                            @else
                                                {{ $resource->type }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($resource->is_active)
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-danger">Inativo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('resources.show', $resource->id) }}" class="btn btn-info btn-sm">Ver</a>
                                                <a href="{{ route('resources.edit', $resource->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                                <form action="{{ route('resources.destroy', $resource->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este recurso?')">Excluir</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhum recurso cadastrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $resources->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
