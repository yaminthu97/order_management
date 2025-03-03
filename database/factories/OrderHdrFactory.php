<?php
namespace Database\Factories;

use App\Models\Order\Base\OrderHdrModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderHdrFactory extends Factory
{
    protected $model = OrderHdrModel::class;

    public function definition()
    {
        return [
            'm_account_id' => 1, // AccountModel
            't_order_hdr_id' => null, // auto_increment
            't_order_destination_id' => $this->faker->randomNumber(),
            'order_destination_seq' => 1,
            'order_dtl_seq' => 1,
            'ecs_id' => null, // EcsModel
            'sell_id' => null, // Ami::factory()
            'order_dtl_coupon_price' => $this->faker->randomFloat(2, 0, 1000),
            'order_sell_price' => $this->faker->randomFloat(2, 0, 1000),
            'order_cost' => $this->faker->randomFloat(2, 0, 1000),
            'order_time_sell_vol' => $this->faker->randomNumber(),
            'order_sell_vol' => $this->faker->randomNumber(),
            'tax_rate' => $this->faker->randomFloat(2, 0, 1),
            'tax_price' => $this->faker->randomFloat(2, 0, 1000),
            'order_return_vol' => $this->faker->randomNumber(),
            'temp_reservation_flg' => $this->faker->numberBetween(0, 1),
            'reservation_date' => $this->faker->dateTime,
            'deli_instruct_date' => $this->faker->dateTime,
            'deli_decision_date' => $this->faker->dateTime,
            't_deli_hdr_id' => $this->faker->randomNumber(),
            'bundle_from_order_id' => $this->faker->randomNumber(),
            'bundle_from_order_dtl_id' => $this->faker->randomNumber(),
            'attachment_item_group_id' => $this->faker->randomNumber(),
            'entry_operator_id' => $this->faker->randomNumber(),
            'entry_timestamp' => $this->faker->dateTime,
            'update_operator_id' => $this->faker->randomNumber(),
            'update_timestamp' => $this->faker->dateTime,
            'cancel_operator_id' => $this->faker->randomNumber(),
            'cancel_timestamp' => $this->faker->dateTime,
        ];
    }
}
