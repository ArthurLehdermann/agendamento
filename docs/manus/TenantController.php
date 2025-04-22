<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * Display a listing of the tenants.
     */
    public function index()
    {
        // Verificar se o usuário é admin
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Acesso não autorizado.');
        }
        
        $tenants = Tenant::orderBy('name')->paginate(10);
        
        return view('tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create()
    {
        // Verificar se o usuário é admin
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Acesso não autorizado.');
        }
        
        return view('tenants.create');
    }

    /**
     * Store a newly created tenant in storage.
     */
    public function store(Request $request)
    {
        // Verificar se o usuário é admin
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Acesso não autorizado.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'business_type' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $tenant = new Tenant($request->all());
        $tenant->slug = Str::slug($request->name);
        $tenant->is_active = $request->has('is_active');
        $tenant->subscription_status = 'trial';
        $tenant->trial_ends_at = now()->addDays(30);
        
        // Processar logo se enviado
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $tenant->logo = $logoPath;
        }
        
        $tenant->save();
        
        return redirect()->route('tenants.index')
            ->with('success', 'Tenant criado com sucesso.');
    }

    /**
     * Display the specified tenant.
     */
    public function show(Tenant $tenant)
    {
        // Verificar se o usuário é admin ou pertence ao tenant
        if (!Auth::user() || (!Auth::user()->isAdmin() && Auth::user()->tenant_id !== $tenant->id)) {
            return redirect()->route('home')->with('error', 'Acesso não autorizado.');
        }
        
        return view('tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Tenant $tenant)
    {
        // Verificar se o usuário é admin ou pertence ao tenant
        if (!Auth::user() || (!Auth::user()->isAdmin() && Auth::user()->tenant_id !== $tenant->id)) {
            return redirect()->route('home')->with('error', 'Acesso não autorizado.');
        }
        
        return view('tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        // Verificar se o usuário é admin ou pertence ao tenant
        if (!Auth::user() || (!Auth::user()->isAdmin() && Auth::user()->tenant_id !== $tenant->id)) {
            return redirect()->route('home')->with('error', 'Acesso não autorizado.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email,' . $tenant->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'business_type' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $tenant->fill($request->all());
        $tenant->slug = Str::slug($request->name);
        $tenant->is_active = $request->has('is_active');
        
        // Processar logo se enviado
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $tenant->logo = $logoPath;
        }
        
        $tenant->save();
        
        return redirect()->route('tenants.index')
            ->with('success', 'Tenant atualizado com sucesso.');
    }

    /**
     * Remove the specified tenant from storage.
     */
    public function destroy(Tenant $tenant)
    {
        // Verificar se o usuário é admin
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Acesso não autorizado.');
        }
        
        $tenant->delete();
        
        return redirect()->route('tenants.index')
            ->with('success', 'Tenant excluído com sucesso.');
    }
}
