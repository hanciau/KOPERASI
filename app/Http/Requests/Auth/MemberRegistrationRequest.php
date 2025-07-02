<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class MemberRegistrationRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        Log::info('MemberRegistrationRequest rules() method called.');
        Log::info('FormRequest Data (all): ' . json_encode($this->all()));
        Log::info('FormRequest Files (all): ' . json_encode($this->files->all()));
        Log::info('FormRequest has slip_gaji_file: ' . ($this->hasFile('slip_gaji_file') ? 'true' : 'false'));
        if ($this->hasFile('slip_gaji_file')) {
            Log::info('FormRequest slip_gaji_file original name: ' . $this->file('slip_gaji_file')->getClientOriginalName());
            Log::info('FormRequest slip_gaji_file mime type: ' . $this->file('slip_gaji_file')->getMimeType());
            Log::info('FormRequest slip_gaji_file size: ' . $this->file('slip_gaji_file')->getSize());
        }

        return [
            'email' => [
                'required',
                'email',
                Rule::unique('members', 'email'),
            ],
            'nama' => 'required|string|max:50', 
            'nip' => 'required|string|max:50',
            'nik' => 'required|string|max:50',
            'jabatan' => 'required|string|max:100',
            'slip_gaji_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', 
            'salary' => 'required|string|max:50',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        Log::error('Validation failed in MemberRegistrationRequest!');
        Log::error('Validation Errors: ' . json_encode($validator->errors()));
        parent::failedValidation($validator); // Penting untuk memanggil parent untuk menjaga perilaku default Laravel (melempar ValidationException)
    }
}
