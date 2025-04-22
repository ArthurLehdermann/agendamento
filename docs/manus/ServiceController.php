<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     */
    public function index(Request $request)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        $services = $tenant->services()->orderBy('name')->paginate(10);
        
        return view('services.index', compact('services', 'tenant'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create()
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        return view('services.create', compact('tenant'));
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $service = new Service($request->all());
        $service->tenant_id = $tenant->id;
        $service->is_active = $request->has('is_active');
        $service->save();
        
        return redirect()->route('services.index')
            ->with('success', 'Serviço criado com sucesso.');
    }

    /**
     * Display the specified service.
     */
    public function show(Service $service)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant || $service->tenant_id !== $tenant->id) {
            return redirect()->route('home')->with('error', 'Serviço não encontrado.');
        }
        
        return view('services.show', compact('service', 'tenant'));
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(Service $service)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant || $service->tenant_id !== $tenant->id) {
            return redirect()->route('home')->with('error', 'Serviço não encontrado.');
        }
        
        return view('services.edit', compact('service', 'tenant'));
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, Service $service)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant || $service->tenant_id !== $tenant->id) {
            return redirect()->route('home')->with('error', 'Serviço não encontrado.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $service->fill($request->all());
        $service->is_active = $request->has('is_active');
        $service->save();
        
        return redirect()->route('services.index')
            ->with('success', 'Serviço atualizado com sucesso.');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Service $service)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant || $service->tenant_id !== $tenant->id) {
            return redirect()->route('home')->with('error', 'Serviço não encontrado.');
        }
        
        $service->delete();
        
        return redirect()->route('services.index')
            ->with('success', 'Serviço excluído com sucesso.');
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
