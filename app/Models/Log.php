<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Log
 * 
 * @property int $id
 * @property Carbon $date
 * @property string $action
 * @property int $action_id
 * @property int $id_user
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Log extends Model
{
	protected $table = 'log';
	public $timestamps = false;

	protected $casts = [
		'date' => 'datetime',
		'action_id' => 'int',
		'id_user' => 'int'
	];

	protected $fillable = [
		'date',
		'action',
		'action_id',
		'id_user'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'id_user');
	}
}
