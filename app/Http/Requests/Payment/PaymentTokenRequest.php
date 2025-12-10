<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentTokenRequest extends FormRequest
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
            'order_id' => ['required', 'string', 'exists:orders,order_id'],
            'providerName' => [
                'required',
                Rule::in(['AYA Pay', 'OK$', 'Sai Sai Pay', 'Onepay', 'MPitesan', 'MPT Pay', 'CB Pay', 'UAB Pay', 'KBZ Pay', 'Wave Pay', 'Visa', 'Master', 'JCB']),
            ],
            'methodName' => [
                'required',
                function ($attribute, $value, $fail) {
                    $providerMethods = [
                        'AYA Pay' => ['QR', 'PIN'],
                        'OK$' => ['PIN'],
                        'Sai Sai Pay' => ['PIN'],
                        'Onepay' => ['PIN'],
                        'MPitesan' => ['PIN'],
                        'MPT Pay' => ['PIN'],
                        'CB Pay' => ['QR'],
                        'UAB Pay' => ['PIN'],
                        'KBZ Pay' => ['QR', 'PWA'],
                        'Wave Pay' => ['PIN'],
                        'Visa' => ['OTP'],
                        'Master' => ['OTP'],
                        'JCB' => ['OTP'],
                    ];

                    $provider = $this->input('providerName');
                    if (! isset($providerMethods[$provider])) {
                        $fail("The selected providerName is invalid.");
                        return;
                    }

                    $normalizedValue = strtoupper(trim($value));
                    $allowedMethods = array_map('strtoupper', $providerMethods[$provider]);

                    if (! in_array($normalizedValue, $allowedMethods)) {
                        $fail("The selected methodName is invalid for {$provider}.");
                    }
                },
            ],
            'customerName' => ['nullable', 'string', 'max:255'],
            'customerPhone' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    $methodName = $this->input('methodName');
                    if (in_array($methodName, ['PIN', 'PWA', 'OTP']) && ! empty($value)) {
                        $phone = preg_replace('/[^\d+]/', '', $value);

                        if (str_starts_with($phone, '+959')) {
                            $phone = '09' . substr($phone, 4);
                        } elseif (str_starts_with($phone, '959')) {
                            $phone = '09' . substr($phone, 3);
                        } elseif (strlen($phone) === 9 && ! str_starts_with($phone, '09')) {
                            $phone = '09' . $phone;
                        }

                        if (! str_starts_with($phone, '09')) {
                            $fail('Phone number must start with 09.');
                        } elseif (strlen($phone) !== 11) {
                            $fail('Phone number must be 11 digits.');
                        } elseif (! preg_match('/^\d+$/', $phone)) {
                            $fail('Phone number must contain only numbers.');
                        }
                    }
                },
            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $providerName = $this->input('providerName');
                    $isCreditCard = in_array($providerName, ['Visa', 'Master', 'JCB']);
                    if ($isCreditCard && empty($value)) {
                        $fail('Email is required for credit card payments.');
                    }
                },
            ],
            'billAddress' => [
                'nullable',
                'string',
                'max:500',
                function ($attribute, $value, $fail) {
                    $providerName = $this->input('providerName');
                    $isCreditCard = in_array($providerName, ['Visa', 'Master', 'JCB']);
                    if ($isCreditCard && empty($value)) {
                        $fail('Billing address is required for credit card payments.');
                    }
                },
            ],
            'billCity' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $providerName = $this->input('providerName');
                    $isCreditCard = in_array($providerName, ['Visa', 'Master', 'JCB']);
                    if ($isCreditCard && empty($value)) {
                        $fail('Billing city is required for credit card payments.');
                    }
                },
            ],
        ];
    }
}



