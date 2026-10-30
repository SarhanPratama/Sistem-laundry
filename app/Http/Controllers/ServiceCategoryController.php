<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::withCount('services')->latest()->get();
        return view('service-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('service-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:service_categories,name',
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
        $request->validate([
            'name'        => 'required|string|max:100|unique:service_categories,name,' . $serviceCategory->id,
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
            ->with('success', 'Kategori berhasil dihapus.');
    }

    public function toggle(ServiceCategory $serviceCategory)
    {
        $serviceCategory->is_active = !$serviceCategory->is_active;
        $serviceCategory->save();

        return redirect()->back()
            ->with('success', $serviceCategory->is_active ? 'Kategori diaktifkan.' : 'Kategori dinonaktifkan.');
    }
}
