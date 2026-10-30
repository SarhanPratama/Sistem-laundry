<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('category')->latest()->get();
        return view('services.index', compact('services'));
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
            ->with('success', 'Layanan berhasil dihapus.');
    }

    public function deactivate(Service $service)
    {
        $service->is_active = !$service->is_active;
        $service->save();

        return redirect()->route('services.index')
            ->with('success', $service->is_active ? 'Layanan berhasil diaktifkan.' : 'Layanan berhasil dinonaktifkan.');
    }
}
