<?php

namespace Database\Factories\Ami\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ami\Base\AmiAttachmentItemModel>
 */
class AmiAttachmentItemModelFactory extends Factory
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
            'attachment_item_cd' => 'CODE' . $this->faker->randomFloat(2, 0, 10000),
            'attachment_item_name' => 'テスト付属品',
            'display_flg' => 1,
            'invoice_flg' => 1,
            'delete_flg' => 0,
        ];
    }
}
