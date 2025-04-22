<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Resource;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard principal com estatísticas e gráficos.
     */
    public function index()
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        // Estatísticas gerais
        $stats = $this->getStats($tenant);
        
        // Dados para gráficos
        $appointmentsByStatus = $this->getAppointmentsByStatus($tenant);
        $appointmentsByDay = $this->getAppointmentsByDay($tenant);
        $appointmentsByResource = $this->getAppointmentsByResource($tenant);
        $appointmentsByService = $this->getAppointmentsByService($tenant);
        
        // Próximos agendamentos
        $upcomingAppointments = $tenant->appointments()
            ->with(['service', 'resource', 'user'])
            ->where('start_time', '>=', Carbon::now())
            ->where('status', '!=', 'canceled')
            ->orderBy('start_time')
            ->limit(5)
            ->get();
        
        // Agendamentos recentes
        $recentAppointments = $tenant->appointments()
            ->with(['service', 'resource', 'user'])
            ->where('start_time', '<', Carbon::now())
            ->orderBy('start_time', 'desc')
            ->limit(5)
            ->get();
        
        return view('dashboard.index', compact(
            'tenant',
            'stats',
            'appointmentsByStatus',
            'appointmentsByDay',
            'appointmentsByResource',
            'appointmentsByService',
            'upcomingAppointments',
            'recentAppointments'
        ));
    }
    
    /**
     * Exibe o dashboard de recursos com disponibilidade e ocupação.
     */
    public function resources()
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        $resources = $tenant->resources()
            ->where('is_active', true)
            ->get();
        
        $resourceStats = [];
        
        foreach ($resources as $resource) {
            $totalAppointments = Appointment::where('resource_id', $resource->id)
                ->where('start_time', '>=', Carbon::now()->startOfMonth())
                ->where('start_time', '<=', Carbon::now()->endOfMonth())
                ->count();
            
            $completedAppointments = Appointment::where('resource_id', $resource->id)
                ->where('status', 'completed')
                ->where('start_time', '>=', Carbon::now()->startOfMonth())
                ->where('start_time', '<=', Carbon::now()->endOfMonth())
                ->count();
            
            $canceledAppointments = Appointment::where('resource_id', $resource->id)
                ->where('status', 'canceled')
                ->where('start_time', '>=', Carbon::now()->startOfMonth())
                ->where('start_time', '<=', Carbon::now()->endOfMonth())
                ->count();
            
            $resourceStats[$resource->id] = [
                'total' => $totalAppointments,
                'completed' => $completedAppointments,
                'canceled' => $canceledAppointments,
                'completion_rate' => $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100) : 0
            ];
        }
        
        return view('dashboard.resources', compact('tenant', 'resources', 'resourceStats'));
    }
    
    /**
     * Exibe o dashboard de serviços com estatísticas de popularidade e receita.
     */
    public function services()
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        $services = $tenant->services()
            ->where('is_active', true)
            ->get();
        
        $serviceStats = [];
        
        foreach ($services as $service) {
            $totalAppointments = Appointment::where('service_id', $service->id)
                ->where('start_time', '>=', Carbon::now()->startOfMonth())
                ->where('start_time', '<=', Carbon::now()->endOfMonth())
                ->count();
            
            $completedAppointments = Appointment::where('service_id', $service->id)
                ->where('status', 'completed')
                ->where('start_time', '>=', Carbon::now()->startOfMonth())
                ->where('start_time', '<=', Carbon::now()->endOfMonth())
                ->count();
            
            $revenue = $completedAppointments * $service->price;
            
            $serviceStats[$service->id] = [
                'total' => $totalAppointments,
                'completed' => $completedAppointments,
                'revenue' => $revenue,
                'popularity' => $totalAppointments
            ];
        }
        
        // Ordenar serviços por popularidade
        $popularServices = $services->sortByDesc(function($service) use ($serviceStats) {
            return $serviceStats[$service->id]['popularity'];
        })->take(5);
        
        // Ordenar serviços por receita
        $topRevenueServices = $services->sortByDesc(function($service) use ($serviceStats) {
            return $serviceStats[$service->id]['revenue'];
        })->take(5);
        
        return view('dashboard.services', compact('tenant', 'services', 'serviceStats', 'popularServices', 'topRevenueServices'));
    }
    
    /**
     * Exibe o dashboard de clientes com estatísticas de frequência e valor.
     */
    public function customers()
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        // Obter todos os clientes únicos a partir dos agendamentos
        $customers = Appointment::where('tenant_id', $tenant->id)
            ->select('customer_name', 'customer_email', 'customer_phone')
            ->distinct()
            ->get();
        
        $customerStats = [];
        
        foreach ($customers as $customer) {
            $appointments = Appointment::where('tenant_id', $tenant->id)
                ->where('customer_name', $customer->customer_name)
                ->where('customer_email', $customer->customer_email)
                ->get();
            
            $totalAppointments = $appointments->count();
            $completedAppointments = $appointments->where('status', 'completed')->count();
            $canceledAppointments = $appointments->where('status', 'canceled')->count();
            
            $totalSpent = 0;
            foreach ($appointments->where('status', 'completed') as $appointment) {
                $totalSpent += $appointment->service->price;
            }
            
            $customerStats[$customer->customer_name] = [
                'email' => $customer->customer_email,
                'phone' => $customer->customer_phone,
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'canceled_appointments' => $canceledAppointments,
                'total_spent' => $totalSpent,
                'last_appointment' => $appointments->sortByDesc('start_time')->first()->start_time ?? null
            ];
        }
        
        // Ordenar clientes por número de agendamentos
        $topCustomers = collect($customerStats)->sortByDesc('total_appointments')->take(10);
        
        // Ordenar clientes por valor gasto
        $topSpenders = collect($customerStats)->sortByDesc('total_spent')->take(10);
        
        return view('dashboard.customers', compact('tenant', 'customers', 'customerStats', 'topCustomers', 'topSpenders'));
    }
    
    /**
     * Obter estatísticas gerais para o dashboard.
     */
    private function getStats($tenant)
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Total de agendamentos
        $totalAppointments = $tenant->appointments()->count();
        
        // Agendamentos de hoje
        $todayAppointments = $tenant->appointments()
            ->whereDate('start_time', $today)
            ->count();
        
        // Agendamentos desta semana
        $weekAppointments = $tenant->appointments()
            ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
            ->count();
        
        // Agendamentos deste mês
        $monthAppointments = $tenant->appointments()
            ->whereBetween('start_time', [$startOfMonth, $endOfMonth])
            ->count();
        
        // Agendamentos por status
        $scheduledAppointments = $tenant->appointments()->where('status', 'scheduled')->count();
        $confirmedAppointments = $tenant->appointments()->where('status', 'confirmed')->count();
        $inProgressAppointments = $tenant->appointments()->where('status', 'in_progress')->count();
        $completedAppointments = $tenant->appointments()->where('status', 'completed')->count();
        $canceledAppointments = $tenant->appointments()->where('status', 'canceled')->count();
        $noShowAppointments = $tenant->appointments()->where('status', 'no_show')->count();
        
        // Total de serviços
        $totalServices = $tenant->services()->count();
        
        // Total de recursos
        $totalResources = $tenant->resources()->count();
        
        // Total de clientes únicos
        $totalCustomers = $tenant->appointments()
            ->select('customer_name', 'customer_email')
            ->distinct()
            ->count();
        
        // Receita estimada (baseada em agendamentos completados)
        $revenue = DB::table('appointments')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->where('appointments.tenant_id', $tenant->id)
            ->where('appointments.status', 'completed')
            ->sum('services.price');
        
        return [
            'total_appointments' => $totalAppointments,
            'today_appointments' => $todayAppointments,
            'week_appointments' => $weekAppointments,
            'month_appointments' => $monthAppointments,
            'scheduled_appointments' => $scheduledAppointments,
            'confirmed_appointments' => $confirmedAppointments,
            'in_progress_appointments' => $inProgressAppointments,
            'completed_appointments' => $completedAppointments,
            'canceled_appointments' => $canceledAppointments,
            'no_show_appointments' => $noShowAppointments,
            'total_services' => $totalServices,
            'total_resources' => $totalResources,
            'total_customers' => $totalCustomers,
            'revenue' => $revenue
        ];
    }
    
    /**
     * Obter dados de agendamentos por status para gráficos.
     */
    private function getAppointmentsByStatus($tenant)
    {
        $statuses = [
            'scheduled' => 'Agendado',
            'confirmed' => 'Confirmado',
            'in_progress' => 'Em Andamento',
            'completed' => 'Concluído',
            'canceled' => 'Cancelado',
            'no_show' => 'Não Compareceu'
        ];
        
        $data = [];
        $colors = [
            'scheduled' => '#3490dc',
            'confirmed' => '#38c172',
            'in_progress' => '#ffed4a',
            'completed' => '#6574cd',
            'canceled' => '#e3342f',
            'no_show' => '#6c757d'
        ];
        
        foreach ($statuses as $status => $label) {
            $count = $tenant->appointments()->where('status', $status)->count();
            
            if ($count > 0) {
                $data[] = [
                    'status' => $label,
                    'count' => $count,
                    'color' => $colors[$status]
                ];
            }
        }
        
        return $data;
    }
    
    /**
     * Obter dados de agendamentos por dia da semana para gráficos.
     */
    private function getAppointmentsByDay($tenant)
    {
        $days = [
            0 => 'Domingo',
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado'
        ];
        
        $data = [];
        
        for ($i = 0; $i < 7; $i++) {
            $count = $tenant->appointments()
                ->whereRaw('DAYOFWEEK(start_time) = ?', [$i == 0 ? 1 : $i + 1])
                ->count();
            
            $data[] = [
                'day' => $days[$i],
                'count' => $count
            ];
        }
        
        return $data;
    }
    
    /**
     * Obter dados de agendamentos por recurso para gráficos.
     */
    private function getAppointmentsByResource($tenant)
    {
        $resources = $tenant->resources()->get();
        $data = [];
        
        foreach ($resources as $resource) {
            $count = Appointment::where('resource_id', $resource->id)->count();
            
            if ($count > 0) {
                $data[] = [
                    'resource' => $resource->name,
                    'count' => $count,
                    'color' => $resource->color ?? '#3490dc'
                ];
            }
        }
        
        return $data;
    }
    
    /**
     * Obter dados de agendamentos por serviço para gráficos.
     */
    private function getAppointmentsByService($tenant)
    {
        $services = $tenant->services()->get();
        $data = [];
        
        foreach ($services as $service) {
            $count = Appointment::where('service_id', $service->id)->count();
            
            if ($count > 0) {
                $data[] = [
                    'service' => $service->name,
                    'count' => $count,
                    'color' => $service->color ?? '#3490dc'
                ];
            }
        }
        
        return $data;
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
