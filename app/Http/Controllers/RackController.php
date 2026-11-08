<?php

namespace App\Http\Controllers;

use App\Models\Rack;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $showTrashed = $request->boolean('trashed');
        $trashedCount = Rack::onlyTrashed()->count();

        $query = $showTrashed ? Rack::onlyTrashed() : Rack::query();
        $racks = $query->withCount([
            'orders' => fn ($q) => $q->where('is_cancelled', false)->where('status', 'siap_diambil'),
        ])->orderBy('name')->get();

        return view('racks.index', compact('racks', 'showTrashed', 'trashedCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('racks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $tenantId = TenantContext::getTenantId();
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('racks', 'name')
                    ->whereNull('deleted_at')
                    ->where(fn ($q) => $tenantId ? $q->where('tenant_id', $tenantId) : $q),
            ],
            'location_description' => 'required|string|max:255',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Nama rak wajib diisi.',
            'name.unique' => 'Nama rak sudah digunakan.',
            'location_description.required' => 'Deskripsi lokasi wajib diisi.',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Rack::create($validated);

        return redirect()->route('racks.index')->with('success', 'Rak baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Rack $rack)
    {
        $orders = $rack->orders()->with(['customer:id,full_name,phone_number'])->latest()->get();
        return view('racks.show', compact('rack', 'orders'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rack $rack)
    {
        return view('racks.edit', compact('rack'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rack $rack)
    {
        $tenantId = TenantContext::getTenantId();
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('racks', 'name')
                    ->ignore($rack->id)
                    ->whereNull('deleted_at')
                    ->where(fn ($q) => $tenantId ? $q->where('tenant_id', $tenantId) : $q),
            ],
            'location_description' => 'required|string|max:255',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Nama rak wajib diisi.',
            'name.unique' => 'Nama rak sudah digunakan.',
            'location_description.required' => 'Deskripsi lokasi wajib diisi.',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $rack->update($validated);

        return redirect()->route('racks.index')->with('success', 'Data rak berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rack $rack)
    {
        if ($rack->orders()->where('is_cancelled', false)->where('status', 'siap_diambil')->count() > 0) {
            return back()->with('error', 'Rak ini masih ditempati cucian Siap Diambil dan tidak dapat dihapus.');
        }

        $rack->delete();
        return redirect()->route('racks.index')->with('success', 'Rak berhasil dipindahkan ke tab Terhapus.');
    }

    public function restore($id)
    {
        $rack = Rack::onlyTrashed()->findOrFail($id);

        if (Rack::where('name', $rack->name)->exists()) {
            return redirect()->back()
                ->with('error', "Tidak dapat memulihkan rak karena nama \"{$rack->name}\" sudah digunakan oleh rak aktif lain.");
        }

        $rack->restore();

        return redirect()->route('racks.index', ['trashed' => 1])
            ->with('success', "Rak {$rack->name} berhasil dipulihkan.");
    }
}
