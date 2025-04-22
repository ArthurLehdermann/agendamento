@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Criar Novo Agendamento</div>

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

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('appointments.store') }}" id="appointmentForm">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="service_id" class="form-label">Serviço</label>
                                <select class="form-select" id="service_id" name="service_id" required>
                                    <option value="">Selecione um serviço</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}" data-duration="{{ $service->duration }}">
                                            {{ $service->name }} ({{ $service->duration }} min - R$ {{ number_format($service->price, 2, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="resource_id" class="form-label">Recurso</label>
                                <select class="form-select" id="resource_id" name="resource_id" required>
                                    <option value="">Selecione um recurso</option>
                                    @foreach($resources as $resource)
                                        <option value="{{ $resource->id }}">
                                            {{ $resource->name }} ({{ $resource->type == 'bay' ? 'Baia' : ($resource->type == 'professional' ? 'Profissional' : $resource->type) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_time" class="form-label">Data e Hora</label>
                                <input type="datetime-local" class="form-control" id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="duration_display" class="form-label">Duração (minutos)</label>
                                <input type="text" class="form-control" id="duration_display" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Nome do Cliente</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer_email" class="form-label">Email do Cliente</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email" value="{{ old('customer_email') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="customer_phone" class="form-label">Telefone do Cliente</label>
                                <input type="text" class="form-control" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Observações</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>

                        <div class="mb-3" id="availability_check" style="display: none;">
                            <div class="alert alert-info">
                                Verificando disponibilidade...
                            </div>
                        </div>

                        <div class="mb-3" id="availability_result" style="display: none;">
                            <!-- Resultado da verificação de disponibilidade será exibido aqui -->
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('appointments.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="button" class="btn btn-info" id="check_availability_btn">Verificar Disponibilidade</button>
                            <button type="submit" class="btn btn-primary" id="submit_btn" disabled>Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceSelect = document.getElementById('service_id');
        const resourceSelect = document.getElementById('resource_id');
        const startTimeInput = document.getElementById('start_time');
        const durationDisplay = document.getElementById('duration_display');
        const checkAvailabilityBtn = document.getElementById('check_availability_btn');
        const submitBtn = document.getElementById('submit_btn');
        const availabilityCheck = document.getElementById('availability_check');
        const availabilityResult = document.getElementById('availability_result');
        
        // Atualizar duração quando o serviço é selecionado
        serviceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const duration = selectedOption.getAttribute('data-duration');
            durationDisplay.value = duration ? duration : '';
        });
        
        // Verificar disponibilidade
        checkAvailabilityBtn.addEventListener('click', function() {
            const serviceId = serviceSelect.value;
            const resourceId = resourceSelect.value;
            const startTime = startTimeInput.value;
            
            if (!serviceId || !resourceId || !startTime) {
                alert('Por favor, preencha todos os campos necessários.');
                return;
            }
            
            availabilityCheck.style.display = 'block';
            availabilityResult.style.display = 'none';
            submitBtn.disabled = true;
            
            // Fazer requisição AJAX para verificar disponibilidade
            fetch(`/appointments/check-availability?service_id=${serviceId}&resource_id=${resourceId}&start_time=${startTime}`)
                .then(response => response.json())
                .then(data => {
                    availabilityCheck.style.display = 'none';
                    availabilityResult.style.display = 'block';
                    
                    if (data.available) {
                        availabilityResult.innerHTML = `
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill"></i> ${data.message}
                            </div>
                        `;
                        submitBtn.disabled = false;
                    } else {
                        availabilityResult.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="bi bi-x-circle-fill"></i> ${data.message}
                            </div>
                        `;
                        submitBtn.disabled = true;
                    }
                })
                .catch(error => {
                    availabilityCheck.style.display = 'none';
                    availabilityResult.innerHTML = `
                        <div class="alert alert-danger">
                            Erro ao verificar disponibilidade. Por favor, tente novamente.
                        </div>
                    `;
                    availabilityResult.style.display = 'block';
                    submitBtn.disabled = true;
                });
        });
        
        // Desabilitar verificação quando os campos são alterados
        [serviceSelect, resourceSelect, startTimeInput].forEach(element => {
            element.addEventListener('change', function() {
                submitBtn.disabled = true;
                availabilityResult.style.display = 'none';
            });
        });
    });
</script>
@endsection
