@extends('layouts.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<style>
    .fc-event {
        cursor: pointer;
    }
    .calendar-container {
        height: 700px;
    }
    .fc-toolbar-title {
        font-size: 1.5em !important;
    }
    .view-buttons {
        margin-bottom: 1rem;
    }
    .resource-filter {
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Calendário de Agendamentos</span>
                    <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm">Novo Agendamento</a>
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

                    <div class="row">
                        <div class="col-md-12">
                            <div class="view-buttons btn-group" role="group">
                                <a href="{{ route('appointments.calendar', ['view' => 'day', 'date' => $date->format('Y-m-d')]) }}" 
                                   class="btn btn-outline-primary {{ $view == 'day' ? 'active' : '' }}">Dia</a>
                                <a href="{{ route('appointments.calendar', ['view' => 'week', 'date' => $date->format('Y-m-d')]) }}" 
                                   class="btn btn-outline-primary {{ $view == 'week' ? 'active' : '' }}">Semana</a>
                                <a href="{{ route('appointments.calendar', ['view' => 'month', 'date' => $date->format('Y-m-d')]) }}" 
                                   class="btn btn-outline-primary {{ $view == 'month' ? 'active' : '' }}">Mês</a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="resource-filter">
                                <label for="resource-select" class="form-label">Filtrar por Recurso:</label>
                                <select id="resource-select" class="form-select">
                                    <option value="">Todos os Recursos</option>
                                    @foreach($resources as $resource)
                                        <option value="{{ $resource->id }}">{{ $resource->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div id="calendar" class="calendar-container"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalhes do agendamento -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appointmentModalLabel">Detalhes do Agendamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="appointmentDetails">
                    <!-- Detalhes do agendamento serão carregados aqui -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="#" class="btn btn-primary" id="viewAppointmentBtn">Ver Detalhes</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/pt-br.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const resourceSelect = document.getElementById('resource-select');
        const appointmentModal = new bootstrap.Modal(document.getElementById('appointmentModal'));
        const viewAppointmentBtn = document.getElementById('viewAppointmentBtn');
        
        // Configuração inicial do calendário
        const initialView = '{{ $view }}View';
        const initialDate = '{{ $date->format('Y-m-d') }}';
        
        // Criar o calendário
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: initialView,
            initialDate: initialDate,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            locale: 'pt-br',
            slotMinTime: '07:00:00',
            slotMaxTime: '20:00:00',
            allDaySlot: false,
            slotDuration: '00:15:00',
            slotLabelInterval: '01:00:00',
            height: 'auto',
            nowIndicator: true,
            navLinks: true,
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5], // Segunda a sexta
                startTime: '08:00',
                endTime: '18:00',
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            },
            events: function(info, successCallback, failureCallback) {
                // Obter agendamentos via AJAX
                const resourceId = resourceSelect.value;
                const url = `/appointments/get-appointments?start=${info.startStr}&end=${info.endStr}${resourceId ? `&resource_id=${resourceId}` : ''}`;
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        successCallback(data);
                    })
                    .catch(error => {
                        failureCallback(error);
                    });
            },
            eventClick: function(info) {
                // Exibir modal com detalhes do agendamento
                const event = info.event;
                const appointmentId = event.id;
                const title = event.title;
                const start = event.start;
                const end = event.end;
                const status = event.extendedProps.status;
                
                let statusText = '';
                let statusClass = '';
                
                switch(status) {
                    case 'scheduled':
                        statusText = 'Agendado';
                        statusClass = 'bg-primary';
                        break;
                    case 'confirmed':
                        statusText = 'Confirmado';
                        statusClass = 'bg-success';
                        break;
                    case 'in_progress':
                        statusText = 'Em Andamento';
                        statusClass = 'bg-warning';
                        break;
                    case 'completed':
                        statusText = 'Concluído';
                        statusClass = 'bg-info';
                        break;
                    case 'canceled':
                        statusText = 'Cancelado';
                        statusClass = 'bg-danger';
                        break;
                    case 'no_show':
                        statusText = 'Não Compareceu';
                        statusClass = 'bg-secondary';
                        break;
                }
                
                const startFormatted = start.toLocaleString('pt-BR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                const endFormatted = end.toLocaleString('pt-BR', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                document.getElementById('appointmentDetails').innerHTML = `
                    <div class="mb-3">
                        <strong>Cliente:</strong> ${title.split(' - ')[0]}
                    </div>
                    <div class="mb-3">
                        <strong>Serviço:</strong> ${title.split(' - ')[1]}
                    </div>
                    <div class="mb-3">
                        <strong>Horário:</strong> ${startFormatted} - ${endFormatted}
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong> <span class="badge ${statusClass}">${statusText}</span>
                    </div>
                `;
                
                viewAppointmentBtn.href = event.url;
                
                appointmentModal.show();
                
                // Prevenir navegação ao clicar no evento
                info.jsEvent.preventDefault();
            }
        });
        
        calendar.render();
        
        // Filtrar por recurso
        resourceSelect.addEventListener('change', function() {
            calendar.refetchEvents();
        });
    });
</script>
@endsection
