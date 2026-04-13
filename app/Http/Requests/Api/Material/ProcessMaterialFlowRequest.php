<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Material;

use Illuminate\Foundation\Http\FormRequest;

class ProcessMaterialFlowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'required_qty' => ['required', 'integer', 'min:1'],

            'item_id' => ['nullable', 'integer', 'exists:items,id'],
            'item_code' => ['nullable', 'string', 'max:50'],
            'item_name' => ['required_without:item_id', 'string', 'max:255'],
            'item_unit' => ['required_without:item_id', 'string', 'max:20'],
            'base_price' => ['required_without:item_id', 'numeric', 'min:0'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],

            'auto_purchase' => ['required', 'boolean'],
            'purchase' => ['nullable', 'array'],
            'purchase.supplier_name' => ['nullable', 'string', 'max:255'],
            'purchase.unit_price' => ['nullable', 'numeric', 'min:0.01'],
            'purchase.qty' => ['nullable', 'integer', 'min:1'],
            'purchase.notes' => ['nullable', 'string', 'max:500'],

            'document_ref' => ['nullable', 'string', 'max:100'],
            'reference_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'auto_purchase' => $this->has('auto_purchase')
                ? filter_var($this->input('auto_purchase'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
                : true,
        ]);
    }
}
