<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThreadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'topic' => 'sometimes',
            'description' => 'required',
            'attachments' => 'sometimes|array|max:5',
            'attachments.*' => 'file|max:1024|mimes:jpeg,png,jpg',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Please provide a content for the thread.',
            'attachments.max' => 'You can only upload up to 5 attachments.',
            'attachments.*.uploaded' => 'Each attachment must not be greater than 1MB.',
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.max' => 'Each attachment must not be greater than 1MB.',
            'attachments.*.mimes' => 'Each attachment must only: jpeg, png, jpg.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $files = $this->file('attachments', []);
            $totalSize = collect($files)->sum(fn ($file) => $file->getSize());
            $maxSize = 5 * 1024 * 1024;

            if ($totalSize > $maxSize) {
                $validator->errors()->add(
                    'attachments',
                    'The total size of all files must not exceed 5MB.'
                );
            }
        });
    }
}
