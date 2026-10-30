<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id'    => 'required|exists:service_categories,id',
            'name'           => 'required|string|max:255',
            'unit'           => 'required|in:kg,item,paket',
            'price_per_unit' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'     => 'Kategori wajib dipilih.',
            'category_id.exists'       => 'Kategori yang dipilih tidak valid.',
            'name.required'           => 'Nama layanan wajib diisi.',
            'name.max'                => 'Nama layanan maksimal 255 karakter.',
            'unit.required'           => 'Satuan wajib dipilih.',
            'unit.in'                 => 'Satuan harus salah satu dari: kg, item, atau paket.',
            'price_per_unit.required' => 'Harga per satuan wajib diisi.',
            'price_per_unit.numeric'  => 'Harga per satuan harus berupa angka.',
            'price_per_unit.min'      => 'Harga per satuan harus lebih dari 0.',
        ];
    }
}
