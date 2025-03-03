<?php


namespace App\Models\Claim\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CreditRegistOutModel
 * 
 * @package App\Models
 */
class CreditRegistOutModel extends Model
{
	protected $table = 'w_credit_regist_out';
	protected $primaryKey = 't_credit_registration_id';
	public $timestamps = false;

}
