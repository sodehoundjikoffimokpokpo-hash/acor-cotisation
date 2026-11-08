<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCarnetLotRequest extends FormRequest
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
        $rules = [
            'batch_number'         => 'required|string|unique:carnet_lots,lot_number',
            'quantity'             => 'required|integer|min:1|max:1000',
            'validity_months'      => 'required|integer|min:1|max:24',
            'penalty_amount'       => 'required|numeric|min:0',
            'card_cost'            => 'required|numeric|min:0',
            'days_covered'         => 'required|integer|min:1',
            'daily_rate'           => 'required|numeric|min:0',
            'article_name'         => 'required|string',
            'purchase_price'       => 'required|numeric|min:0',
            'transport_fees'       => 'required|numeric|min:0',
            'search_fees'          => 'required|numeric|min:0',
            'collector_commission' => 'required|numeric|min:0',
            'admin_fees'           => 'required|numeric|min:0',
            'price_provision'      => 'required|numeric|min:0',
            'acor_margin'          => 'required|numeric|min:0',
        ];

        if (!Auth::user()->country_id) {
            $rules['country_id'] = 'required|exists:countries,id';
        }

        return $rules;
    }
}
