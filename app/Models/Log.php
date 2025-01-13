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
 * @property int $fonctionnalite_id
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
		'fonctionnalite_id' => 'int',
		'id_user' => 'int'
	];

	protected $fillable = [
		'date',
		'action',
		'fonctionnalite_id',
		'id_user'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'id_user');
	}

	public function fonctionnalite()
	{
		return $this->belongsTo(Fonctionnalite::class, 'fonctionnalite_id');
	}
}
