<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resources.
     */
    public function index(Request $request)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        $resources = $tenant->resources()->orderBy('name')->paginate(10);
        
        return view('resources.index', compact('resources', 'tenant'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        return view('resources.create', compact('tenant'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant) {
            return redirect()->route('home')->with('error', 'Tenant não encontrado.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'working_hours' => 'nullable|json',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $resource = new Resource($request->all());
        $resource->tenant_id = $tenant->id;
        $resource->is_active = $request->has('is_active');
        
        // Processar horários de trabalho
        if ($request->has('working_hours') && is_array($request->working_hours)) {
            $resource->working_hours = json_encode($request->working_hours);
        }
        
        $resource->save();
        
        return redirect()->route('resources.index')
            ->with('success', 'Recurso criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Resource $resource)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant || $resource->tenant_id !== $tenant->id) {
            return redirect()->route('home')->with('error', 'Recurso não encontrado.');
        }
        
        return view('resources.show', compact('resource', 'tenant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Resource $resource)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant || $resource->tenant_id !== $tenant->id) {
            return redirect()->route('home')->with('error', 'Recurso não encontrado.');
        }
        
        return view('resources.edit', compact('resource', 'tenant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Resource $resource)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant || $resource->tenant_id !== $tenant->id) {
            return redirect()->route('home')->with('error', 'Recurso não encontrado.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'working_hours' => 'nullable|json',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $resource->fill($request->all());
        $resource->is_active = $request->has('is_active');
        
        // Processar horários de trabalho
        if ($request->has('working_hours') && is_array($request->working_hours)) {
            $resource->working_hours = json_encode($request->working_hours);
        }
        
        $resource->save();
        
        return redirect()->route('resources.index')
            ->with('success', 'Recurso atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resource $resource)
    {
        $tenant = $this->getTenant();
        
        if (!$tenant || $resource->tenant_id !== $tenant->id) {
            return redirect()->route('home')->with('error', 'Recurso não encontrado.');
        }
        
        $resource->delete();
        
        return redirect()->route('resources.index')
            ->with('success', 'Recurso excluído com sucesso.');
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
