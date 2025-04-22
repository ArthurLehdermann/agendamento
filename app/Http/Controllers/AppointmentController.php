<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Resource;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the appointments.
     */
    public function index(Request $request)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        $appointments = $tenant->appointments()
            ->with(['service', 'resource', 'user'])
            ->orderBy('start_time', 'desc')
            ->paginate(10);
        
        return view('appointments.index', compact('appointments', 'tenant'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        $services = $tenant->services()->where('is_active', true)->get();
        $resources = $tenant->resources()->where('is_active', true)->get();
        
        return view('appointments.create', compact('tenant', 'services', 'resources'));
    }

    /**
     * Store a newly created appointment in storage.
     */
    public function store(Request $request)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'resource_id' => 'required|exists:resources,id',
            'start_time' => 'required|date',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Verificar se o serviço e o recurso pertencem ao tenant
        $service = Service::find($request->service_id);
        $resource = Resource::find($request->resource_id);
        
        if (!$service || $service->tenant_id !== $tenant->id || !$resource || $resource->tenant_id !== $tenant->id) {
            return redirect()->back()
                ->with('error', 'Serviço ou recurso inválido.')
                ->withInput();
        }
        
        // Calcular horário de término com base na duração do serviço
        $startTime = Carbon::parse($request->start_time);
        $endTime = $startTime->copy()->addMinutes($service->duration);
        
        // Verificar disponibilidade do recurso
        $conflictingAppointments = Appointment::where('resource_id', $resource->id)
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->count();
        
        if ($conflictingAppointments > 0) {
            return redirect()->back()
                ->with('error', 'O recurso selecionado não está disponível no horário escolhido.')
                ->withInput();
        }
        
        // Verificar se o horário está dentro do horário de trabalho do recurso
        $workingHours = $resource->working_hours;
        if (is_string($workingHours)) {
            $workingHours = json_decode($workingHours, true) ?? [];
        }
        
        $dayOfWeek = strtolower($startTime->englishDayOfWeek);
        
        if (!isset($workingHours[$dayOfWeek]['enabled']) || !$workingHours[$dayOfWeek]['enabled']) {
            return redirect()->back()
                ->with('error', 'O recurso não está disponível neste dia da semana.')
                ->withInput();
        }
        
        $workStart = Carbon::parse($workingHours[$dayOfWeek]['start']);
        $workEnd = Carbon::parse($workingHours[$dayOfWeek]['end']);
        
        $appointmentStart = Carbon::parse($startTime->format('H:i'));
        $appointmentEnd = Carbon::parse($endTime->format('H:i'));
        
        if ($appointmentStart < $workStart || $appointmentEnd > $workEnd) {
            return redirect()->back()
                ->with('error', 'O horário selecionado está fora do horário de trabalho do recurso.')
                ->withInput();
        }
        
        // Criar o agendamento
        $appointment = new Appointment();
        $appointment->tenant_id = $tenant->id;
        $appointment->service_id = $service->id;
        $appointment->resource_id = $resource->id;
        $appointment->user_id = Auth::id();
        $appointment->start_time = $startTime;
        $appointment->end_time = $endTime;
        $appointment->status = 'scheduled';
        $appointment->customer_name = $request->customer_name;
        $appointment->customer_email = $request->customer_email;
        $appointment->customer_phone = $request->customer_phone;
        $appointment->notes = $request->notes;
        $appointment->save();
        
        return redirect()->route('appointments.index')
            ->with('success', 'Agendamento criado com sucesso.');
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant || $appointment->tenant_id !== $tenant->id) {
            return redirect()->route('home')->with('error', 'Agendamento não encontrado.');
        }
        
        return view('appointments.show', compact('appointment', 'tenant'));
    }

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit(Appointment $appointment)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant || $appointment->tenant_id !== $tenant->id) {
            return redirect()->route('home')->with('error', 'Agendamento não encontrado.');
        }
        
        $services = $tenant->services()->where('is_active', true)->get();
        $resources = $tenant->resources()->where('is_active', true)->get();
        
        return view('appointments.edit', compact('appointment', 'tenant', 'services', 'resources'));
    }

    /**
     * Update the specified appointment in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant || $appointment->tenant_id !== $tenant->id) {
            return redirect()->route('home')->with('error', 'Agendamento não encontrado.');
        }
        
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'resource_id' => 'required|exists:resources,id',
            'start_time' => 'required|date',
            'status' => 'required|in:scheduled,confirmed,in_progress,completed,canceled,no_show',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Verificar se o serviço e o recurso pertencem ao tenant
        $service = Service::find($request->service_id);
        $resource = Resource::find($request->resource_id);
        
        if (!$service || $service->tenant_id !== $tenant->id || !$resource || $resource->tenant_id !== $tenant->id) {
            return redirect()->back()
                ->with('error', 'Serviço ou recurso inválido.')
                ->withInput();
        }
        
        // Calcular horário de término com base na duração do serviço
        $startTime = Carbon::parse($request->start_time);
        $endTime = $startTime->copy()->addMinutes($service->duration);
        
        // Verificar disponibilidade do recurso (excluindo o próprio agendamento)
        $conflictingAppointments = Appointment::where('resource_id', $resource->id)
            ->where('id', '!=', $appointment->id)
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->count();
        
        if ($conflictingAppointments > 0) {
            return redirect()->back()
                ->with('error', 'O recurso selecionado não está disponível no horário escolhido.')
                ->withInput();
        }
        
        // Verificar se o horário está dentro do horário de trabalho do recurso
        $workingHours = $resource->working_hours;
        if (is_string($workingHours)) {
            $workingHours = json_decode($workingHours, true) ?? [];
        }
        
        $dayOfWeek = strtolower($startTime->englishDayOfWeek);
        
        if (!isset($workingHours[$dayOfWeek]['enabled']) || !$workingHours[$dayOfWeek]['enabled']) {
            return redirect()->back()
                ->with('error', 'O recurso não está disponível neste dia da semana.')
                ->withInput();
        }
        
        $workStart = Carbon::parse($workingHours[$dayOfWeek]['start']);
        $workEnd = Carbon::parse($workingHours[$dayOfWeek]['end']);
        
        $appointmentStart = Carbon::parse($startTime->format('H:i'));
        $appointmentEnd = Carbon::parse($endTime->format('H:i'));
        
        if ($appointmentStart < $workStart || $appointmentEnd > $workEnd) {
            return redirect()->back()
                ->with('error', 'O horário selecionado está fora do horário de trabalho do recurso.')
                ->withInput();
        }
        
        // Atualizar o agendamento
        $appointment->service_id = $service->id;
        $appointment->resource_id = $resource->id;
        $appointment->start_time = $startTime;
        $appointment->end_time = $endTime;
        $appointment->status = $request->status;
        $appointment->customer_name = $request->customer_name;
        $appointment->customer_email = $request->customer_email;
        $appointment->customer_phone = $request->customer_phone;
        $appointment->notes = $request->notes;
        $appointment->save();
        
        return redirect()->route('appointments.index')
            ->with('success', 'Agendamento atualizado com sucesso.');
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant || $appointment->tenant_id !== $tenant->id) {
            return redirect()->route('home')->with('error', 'Agendamento não encontrado.');
        }
        
        $appointment->delete();
        
        return redirect()->route('appointments.index')
            ->with('success', 'Agendamento excluído com sucesso.');
    }
    
    /**
     * Display the calendar view.
     */
    public function calendar(Request $request)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        $resources = $tenant->resources()->where('is_active', true)->get();
        $services = $tenant->services()->where('is_active', true)->get();
        
        // Determinar a visualização (dia, semana, mês)
        $view = $request->get('view', 'week');
        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();
        
        return view('appointments.calendar', compact('tenant', 'resources', 'services', 'view', 'date'));
    }
    
    /**
     * Get appointments for calendar (JSON).
     */
    public function getAppointments(Request $request)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant não encontrado.'], 404);
        }
        
        $start = $request->get('start');
        $end = $request->get('end');
        $resourceId = $request->get('resource_id');
        
        $query = $tenant->appointments()
            ->with(['service', 'resource', 'user'])
            ->whereBetween('start_time', [$start, $end]);
        
        if ($resourceId) {
            $query->where('resource_id', $resourceId);
        }
        
        $appointments = $query->get();
        
        $events = [];
        
        foreach ($appointments as $appointment) {
            $events[] = [
                'id' => $appointment->id,
                'title' => $appointment->customer_name . ' - ' . $appointment->service->name,
                'start' => $appointment->start_time->toIso8601String(),
                'end' => $appointment->end_time->toIso8601String(),
                'resourceId' => $appointment->resource_id,
                'color' => $appointment->service->color ?? '#3490dc',
                'status' => $appointment->status,
                'url' => route('appointments.show', $appointment->id)
            ];
        }
        
        return response()->json($events);
    }
    
    /**
     * Check resource availability (JSON).
     */
    public function checkAvailability(Request $request)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return response()->json(['available' => false, 'message' => 'Tenant não encontrado.']);
        }
        
        $validator = Validator::make($request->all(), [
            'resource_id' => 'required|exists:resources,id',
            'service_id' => 'required|exists:services,id',
            'start_time' => 'required|date',
            'appointment_id' => 'nullable|exists:appointments,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['available' => false, 'message' => 'Dados inválidos.']);
        }
        
        $resource = Resource::find($request->resource_id);
        $service = Service::find($request->service_id);
        
        if (!$resource || $resource->tenant_id !== $tenant->id || !$service || $service->tenant_id !== $tenant->id) {
            return response()->json(['available' => false, 'message' => 'Recurso ou serviço inválido.']);
        }
        
        $startTime = Carbon::parse($request->start_time);
        $endTime = $startTime->copy()->addMinutes($service->duration);
        
        // Verificar se o horário está dentro do horário de trabalho do recurso
        $workingHours = $resource->working_hours;
        if (is_string($workingHours)) {
            $workingHours = json_decode($workingHours, true) ?? [];
        }
        
        $dayOfWeek = strtolower($startTime->englishDayOfWeek);
        
        if (!isset($workingHours[$dayOfWeek]['enabled']) || !$workingHours[$dayOfWeek]['enabled']) {
            return response()->json(['available' => false, 'message' => 'O recurso não está disponível neste dia da semana.']);
        }
        
        $workStart = Carbon::parse($workingHours[$dayOfWeek]['start']);
        $workEnd = Carbon::parse($workingHours[$dayOfWeek]['end']);
        
        $appointmentStart = Carbon::parse($startTime->format('H:i'));
        $appointmentEnd = Carbon::parse($endTime->format('H:i'));
        
        if ($appointmentStart < $workStart || $appointmentEnd > $workEnd) {
            return response()->json(['available' => false, 'message' => 'O horário selecionado está fora do horário de trabalho do recurso.']);
        }
        
        // Verificar conflitos com outros agendamentos
        $query = Appointment::where('resource_id', $resource->id)
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            });
        
        // Excluir o próprio agendamento se estiver editando
        if ($request->appointment_id) {
            $query->where('id', '!=', $request->appointment_id);
        }
        
        $conflictingAppointments = $query->count();
        
        if ($conflictingAppointments > 0) {
            return response()->json(['available' => false, 'message' => 'O recurso já está agendado para este horário.']);
        }
        
        return response()->json(['available' => true, 'message' => 'Horário disponível.']);
    }
    
    /**
     * Get the current tenant based on authenticated user.
     */
    private function getTenant()
    {
        $user = Auth::user();
        
        if (!$user || !$user->tenant_id) {
            // If user is admin without tenant, get tenant from request
            if ($user && $user->isAdmin()) {
                $tenantId = request()->get('tenant_id');
                if ($tenantId) {
                    return Tenant::find($tenantId);
                }
            }
            return null;
        }
        
        return $user->tenant;
    }
}
