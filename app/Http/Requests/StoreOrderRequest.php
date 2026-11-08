<?php

namespace App\Http\Requests;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerExists = Rule::exists('customers', 'id');
        $serviceExists = Rule::exists('services', 'id');

        if (TenantContext::hasTenant()) {
            $tenantId = TenantContext::getTenantId();
            $customerExists->where('tenant_id', $tenantId);
            $serviceExists->where('tenant_id', $tenantId);
        }

        return [
            'customer_id'          => ['required', $customerExists],
            'received_date'        => ['required', 'date'],
            'estimated_done_date'  => ['required', 'date', 'after_or_equal:received_date'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.service_id'   => ['required', $serviceExists],
            'items.*.quantity'     => ['required', 'numeric', 'min:0.01'],
            'pay_now'              => ['nullable', 'boolean'],
            'payment_method'       => ['nullable', 'required_if:pay_now,1', 'in:tunai,transfer'],
            'payment_amount'       => ['nullable', 'required_if:pay_now,1', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required'         => 'Pelanggan wajib dipilih.',
            'customer_id.exists'           => 'Pelanggan yang dipilih tidak ditemukan.',
            'received_date.required'       => 'Tanggal penerimaan wajib diisi.',
            'received_date.date'           => 'Format tanggal penerimaan tidak valid.',
            'estimated_done_date.required' => 'Estimasi tanggal selesai wajib diisi.',
            'estimated_done_date.date'     => 'Format estimasi tanggal selesai tidak valid.',
            'estimated_done_date.after_or_equal' => 'Estimasi tanggal selesai harus sama dengan atau setelah tanggal penerimaan.',
            'items.required'               => 'Order harus memiliki minimal satu item layanan.',
            'items.array'                  => 'Format item layanan tidak valid.',
            'items.min'                    => 'Order harus memiliki minimal satu item layanan.',
            'items.*.service_id.required'  => 'Layanan wajib dipilih untuk setiap item.',
            'items.*.service_id.exists'    => 'Layanan yang dipilih tidak ditemukan atau tidak aktif.',
            'items.*.quantity.required'    => 'Jumlah/berat wajib diisi untuk setiap item.',
            'items.*.quantity.numeric'     => 'Jumlah/berat harus berupa angka.',
            'items.*.quantity.min'         => 'Jumlah/berat minimal adalah 0.01.',
        ];
    }
}
