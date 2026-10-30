<?php

namespace App\Http\Controllers;

use App\Models\Rack;
use Illuminate\Http\Request;

class RackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $racks = Rack::orderBy('name')->get();
        return view('racks.index', compact('racks'));
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
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:racks',
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
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:racks,name,' . $rack->id,
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
        if ($rack->orders()->count() > 0) {
            return back()->with('error', 'Rak ini masih dikaitkan dengan pesanan dan tidak dapat dihapus.');
        }

        $rack->delete();
        return redirect()->route('racks.index')->with('success', 'Rak berhasil dihapus.');
    }
}
