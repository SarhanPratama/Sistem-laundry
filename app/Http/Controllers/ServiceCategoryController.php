<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        $showTrashed = $request->boolean('trashed');
        $trashedCount = ServiceCategory::onlyTrashed()->count();

        $query = $showTrashed ? ServiceCategory::onlyTrashed() : ServiceCategory::query();
        $categories = $query->withCount('services')->latest($showTrashed ? 'deleted_at' : 'created_at')->get();

        return view('service-categories.index', compact('categories', 'showTrashed', 'trashedCount'));
    }

    public function create()
    {
        return view('service-categories.create');
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::getTenantId();
        $request->validate([
            'name'        => [
                'required',
                'string',
                'max:100',
                Rule::unique('service_categories', 'name')
                    ->whereNull('deleted_at')
                    ->where(fn ($q) => $tenantId ? $q->where('tenant_id', $tenantId) : $q),
            ],
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori sudah digunakan.',
        ]);

        ServiceCategory::create([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => true,
        ]);

        return redirect()->route('service-categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(ServiceCategory $serviceCategory)
    {
        return view('service-categories.edit', compact('serviceCategory'));
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $tenantId = TenantContext::getTenantId();
        $request->validate([
            'name'        => [
                'required',
                'string',
                'max:100',
                Rule::unique('service_categories', 'name')
                    ->ignore($serviceCategory->id)
                    ->whereNull('deleted_at')
                    ->where(fn ($q) => $tenantId ? $q->where('tenant_id', $tenantId) : $q),
            ],
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori sudah digunakan.',
        ]);

        $serviceCategory->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('service-categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        $servicesCount = $serviceCategory->services()->count();

        if ($servicesCount > 0) {
            return redirect()->route('service-categories.index')
                ->with('error', "Kategori tidak bisa dihapus karena masih dipakai oleh {$servicesCount} layanan. Pindahkan layanan tersebut ke kategori lain atau nonaktifkan kategorinya.");
        }

        $serviceCategory->delete();

        return redirect()->route('service-categories.index')
            ->with('success', 'Kategori berhasil dipindahkan ke tab Terhapus.');
    }

    public function restore($id)
    {
        $serviceCategory = ServiceCategory::onlyTrashed()->findOrFail($id);

        if (ServiceCategory::where('name', $serviceCategory->name)->exists()) {
            return redirect()->back()
                ->with('error', "Tidak dapat memulihkan kategori karena nama \"{$serviceCategory->name}\" sudah digunakan oleh kategori aktif lain.");
        }

        $serviceCategory->restore();

        return redirect()->route('service-categories.index', ['trashed' => 1])
            ->with('success', "Kategori {$serviceCategory->name} berhasil dipulihkan.");
    }

    public function toggle(ServiceCategory $serviceCategory)
    {
        $serviceCategory->is_active = !$serviceCategory->is_active;
        $serviceCategory->save();

        return redirect()->back()
            ->with('success', $serviceCategory->is_active ? 'Kategori diaktifkan.' : 'Kategori dinonaktifkan.');
    }
}
