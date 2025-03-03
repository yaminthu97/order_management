<?php

namespace App\Http\Requests\Master\Gfh1207;

use App\Http\Requests\Master\Base\ShopGfhRequest as BaseShopGfhRequest;

class ShopGfhRequest extends BaseShopGfhRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'payment_due_dates'                 => ['required', 'integer', 'min:0', 'max:2147483647'],
            'mail_address_festa_sales'          => ['required', 'string', 'max:2000','regex:/^([\w.+-]+@[\w.-]+\.[a-zA-Z]{2,},\s*)*([\w.+-]+@[\w.-]+\.[a-zA-Z]{2,})$/'],
            'mail_address_festa_inspection'     => ['required', 'string', 'max:2000','regex:/^([\w.+-]+@[\w.-]+\.[a-zA-Z]{2,},\s*)*([\w.+-]+@[\w.-]+\.[a-zA-Z]{2,})$/'],
            'mail_address_prod_dept'            => ['required', 'string', 'max:2000','regex:/^([\w.+-]+@[\w.-]+\.[a-zA-Z]{2,},\s*)*([\w.+-]+@[\w.-]+\.[a-zA-Z]{2,})$/'],
            'mail_address_ec_uriage'            => ['required', 'string', 'max:2000','regex:/^([\w.+-]+@[\w.-]+\.[a-zA-Z]{2,},\s*)*([\w.+-]+@[\w.-]+\.[a-zA-Z]{2,})$/'],
            'mail_address_accounting_dept'      => ['required', 'string', 'max:2000','regex:/^([\w.+-]+@[\w.-]+\.[a-zA-Z]{2,},\s*)*([\w.+-]+@[\w.-]+\.[a-zA-Z]{2,})$/'],
            'mail_address_from'                 => ['required', 'string', 'max:2000','regex:/^([\w.+-]+@[\w.-]+\.[a-zA-Z]{2,},\s*)*([\w.+-]+@[\w.-]+\.[a-zA-Z]{2,})$/'],
            'ftp_server_host_yamato'            => ['required', 'string', 'max:256'],
            'ftp_server_user_yamato'            => ['required', 'string', 'max:256'],
            'ftp_server_password_yamato'        => ['required', 'string', 'max:256'],
            'ecbeing_api_base_url'              => ['required', 'string', 'max:256'],
            'ecbeing_api_exp_customer'          => ['required', 'string', 'max:256'],
            'ecbeing_api_dl_customer'           => ['required', 'string', 'max:256'],
            'ecbeing_api_exp_sales'             => ['required', 'string', 'max:256'],
            'ecbeing_api_dl_sales'              => ['required', 'string', 'max:256'],
            'ecbeing_api_imp_ship'              => ['required', 'string', 'max:256'],
            'ecbeing_api_update_ship'           => ['required', 'string', 'max:256'],
            'ecbeing_api_imp_nyukin'            => ['required', 'string', 'max:256'],
            'ecbeing_api_update_nyukin'         => ['required', 'string', 'max:256'],
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'operator_id' => $this->esmSessionManager->getOperatorId(),
        ]);
    }
}
