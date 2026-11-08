<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $showTrashed = $request->boolean('trashed');
        $trashedCount = Customer::onlyTrashed()->count();

        $query = $showTrashed ? Customer::onlyTrashed() : Customer::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('phone_number', 'LIKE', '%' . $search . '%');
            });
        }

        $customers = $query->withCount('orders')
            ->withSum('orders', 'total_cost')
            ->latest($showTrashed ? 'deleted_at' : 'created_at')
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search', 'showTrashed', 'trashedCount'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create([
            'full_name'    => $request->full_name,
            'phone_number' => $request->phone_number,
            'address'      => $request->address,
            'is_active'    => true,
        ]);

        if ($request->boolean('from_order')) {
            return redirect()->route('orders.create', ['customer_id' => $customer->id])
                ->with('success', 'Pelanggan ' . $customer->full_name . ' berhasil ditambahkan dan langsung dipilih.');
        }

        return redirect()->route('customers.index')
            ->with('success', 'Data pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['orders' => function ($q) {
            $q->latest();
        }]);

        $totalOrders = $customer->orders->count();
        $totalSpent  = $customer->orders->sum('total_cost');

        return view('customers.show', compact('customer', 'totalOrders', 'totalSpent'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update([
            'full_name'    => $request->full_name,
            'phone_number' => $request->phone_number,
            'address'      => $request->address,
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Data pelanggan berhasil dipindahkan ke tab Terhapus.');
    }

    public function restore($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);

        if (Customer::where('phone_number', $customer->phone_number)->exists()) {
            return redirect()->back()
                ->with('error', "Tidak dapat memulihkan pelanggan karena nomor telepon {$customer->phone_number} sudah digunakan oleh pelanggan aktif lain.");
        }

        $customer->restore();

        return redirect()->route('customers.index', ['trashed' => 1])
            ->with('success', "Pelanggan {$customer->full_name} berhasil dipulihkan.");
    }

    public function deactivate(Customer $customer)
    {
        $customer->is_active = false;
        $customer->save();

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil dinonaktifkan.');
    }
}
