<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Droit extends Model
{
	protected $table = 'droits';
	public $timestamps = false;

	protected $casts = [
		'fonctionnalite_id' => 'int',
		'role_id' => 'int'
	];

	protected $fillable = [
		'description',
		'fonctionnalite_id',
		'role_id'
	];

	public function fonctionnalite()
	{
		return $this->belongsTo(Fonctionnalite::class);
	}

	public function role()
	{
		return $this->belongsTo(Role::class);
	}
}