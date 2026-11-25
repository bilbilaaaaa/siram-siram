<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimpanJadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jam' => ['required', 'date_format:H:i'],
            'durasi' => ['required', 'integer', 'min:1', 'max:120'],
            'hari' => ['required', 'array', 'min:1'],
            'hari.*' => ['required', 'integer', 'between:1,7', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jam.required' => 'Jam mulai wajib diisi.',
            'jam.date_format' => 'Gunakan format jam 24 jam, misalnya 06:30.',
            'durasi.required' => 'Durasi penyiraman wajib diisi.',
            'durasi.integer' => 'Durasi harus berupa angka bulat dalam menit.',
            'durasi.min' => 'Durasi minimal 1 menit.',
            'durasi.max' => 'Durasi maksimal 120 menit.',
            'hari.required' => 'Pilih minimal satu hari penyiraman.',
            'hari.array' => 'Pilihan hari tidak valid.',
            'hari.min' => 'Pilih minimal satu hari penyiraman.',
            'hari.*.required' => 'Setiap pilihan hari wajib diisi.',
            'hari.*.integer' => 'Pilihan hari tidak valid.',
            'hari.*.between' => 'Pilih hari antara Senin dan Minggu.',
            'hari.*.distinct' => 'Hari yang sama tidak boleh dipilih lebih dari sekali.',
        ];
    }
}
