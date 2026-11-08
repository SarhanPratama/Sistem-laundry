<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $showTrashed = $request->boolean('trashed');
        $trashedCount = Service::onlyTrashed()->count();

        $query = $showTrashed ? Service::onlyTrashed() : Service::query();
        $services = $query->with('category')->latest($showTrashed ? 'deleted_at' : 'created_at')->get();

        return view('services.index', compact('services', 'showTrashed', 'trashedCount'));
    }

    public function create()
    {
        $categories = ServiceCategory::where('is_active', true)->orderBy('name')->get();
        return view('services.create', compact('categories'));
    }

    public function store(StoreServiceRequest $request)
    {
        Service::create([
            'category_id'    => $request->category_id,
            'name'           => $request->name,
            'unit'           => $request->unit,
            'price_per_unit' => $request->price_per_unit,
            'is_active'      => true,
        ]);

        return redirect()->route('services.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service)
    {
        $categories = ServiceCategory::where('is_active', true)->orderBy('name')->get();
        return view('services.edit', compact('service', 'categories'));
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $service->update([
            'category_id'    => $request->category_id,
            'name'           => $request->name,
            'unit'           => $request->unit,
            'price_per_unit' => $request->price_per_unit,
        ]);

        return redirect()->route('services.index')
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('services.index')
            ->with('success', 'Layanan berhasil dipindahkan ke tab Terhapus.');
    }

    public function restore($id)
    {
        $service = Service::onlyTrashed()->findOrFail($id);
        $service->restore();

        return redirect()->route('services.index', ['trashed' => 1])
            ->with('success', "Layanan {$service->name} berhasil dipulihkan.");
    }

    public function deactivate(Service $service)
    {
        $service->is_active = !$service->is_active;
        $service->save();

        return redirect()->route('services.index')
            ->with('success', $service->is_active ? 'Layanan berhasil diaktifkan.' : 'Layanan berhasil dinonaktifkan.');
    }
}
