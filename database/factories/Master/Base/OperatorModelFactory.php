<?php

namespace Database\Factories\Master\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Base\OperatorModel>
 */
class OperatorModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'delete_flg' => 0,
            'user_type' => 99,
            'm_operation_authority_id' => 1,
            'login_id' => "sc360-admin",
            'login_password' => '$2y$10$lo3YZxJtWAh65qlR1fxuZ.sMfFbE3BygWct7h4VFGi2AbM6l4Wg1W', //password
            'm_operator_name' => "SC360管理者",
        ];
    }
}
