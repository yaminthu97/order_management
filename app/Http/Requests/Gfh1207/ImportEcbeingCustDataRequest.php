<?php

namespace App\Http\Requests\Gfh1207;

use Illuminate\Console\Command;

class ImportEcbeingCustDataRequest extends Command
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
            'reserve10'   =>      ['required', 'string', 'max:100'],
            'name_kanji'   =>      ['required', 'string', 'max:100'],
            'sex_type'   =>      ['required', 'integer', 'in:1,2'],
            'postal'   =>      ['required', 'string', 'max:7'],
            'address1'   =>      ['required', 'string', 'max:100'],
            'address2'   =>      ['required', 'string', 'max:100'],
            'address3'   =>      ['required', 'string', 'max:100'],
            'tel1'   =>      ['required', 'string', 'max:20'],
            'dm_send_mail_flg'   =>      ['required', 'integer', 'in:0,1'],
            'dm_send_letter_flg'   =>      ['required', 'integer', 'in:0,1'],
            'delete_flg'   =>      ['required', 'integer', 'in:0,1'],
        ];
    }
}
