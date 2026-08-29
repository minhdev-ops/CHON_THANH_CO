<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:150',
                'regex:/^[\p{L}\p{M}\s.\'-]+$/u',
            ],
            'phone' => [
                'required',
                'string',
                'min:9',
                'max:20',
                'regex:/^[0-9+][0-9+\-\s().]*$/',
            ],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'min:2', 'max:150'],
            'product' => ['nullable', 'string', 'max:150'],
            'products' => ['nullable', 'array', 'max:20'],
            'products.*' => ['string', 'max:150', 'distinct'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',
            'name.min' => 'Họ tên phải có ít nhất 2 ký tự.',
            'name.max' => 'Họ tên không được vượt quá 150 ký tự.',
            'name.regex' => 'Họ tên chỉ được chứa chữ cái (bao gồm tiếng Việt) và khoảng trắng, không được chứa số hoặc ký tự đặc biệt.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.min' => 'Số điện thoại phải có ít nhất 9 ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'phone.regex' => 'Số điện thoại chỉ được chứa chữ số, dấu +, dấu - và khoảng trắng.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ, phải chứa dấu @.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'company.min' => 'Tên công ty phải có ít nhất 2 ký tự.',
            'company.max' => 'Tên công ty không được vượt quá 150 ký tự.',
            'products.max' => 'Chỉ được chọn tối đa 20 sản phẩm.',
            'products.*.max' => 'Tên sản phẩm không được vượt quá 150 ký tự.',
            'products.*.distinct' => 'Sản phẩm đã được chọn trước đó.',
            'message.required' => 'Vui lòng nhập nội dung tư vấn.',
            'message.min' => 'Nội dung tư vấn phải có ít nhất 5 ký tự.',
            'message.max' => 'Nội dung tư vấn không được vượt quá 5000 ký tự.',
        ];
    }
}
