<?php

namespace App\Http\Requests;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uniqueRule = Rule::unique('customers', 'phone_number')->whereNull('deleted_at');
        if (TenantContext::hasTenant()) {
            $uniqueRule->where('tenant_id', TenantContext::getTenantId());
        }

        return [
            'full_name'    => 'required|string|max:255',
            'phone_number' => ['required', 'string', 'max:20', $uniqueRule],
            'address'      => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required'    => 'Nama lengkap wajib diisi.',
            'phone_number.required' => 'Nomor telepon wajib diisi.',
            'phone_number.unique'   => 'Nomor telepon sudah terdaftar dalam sistem.',
        ];
    }
}
