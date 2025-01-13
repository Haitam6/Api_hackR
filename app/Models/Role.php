<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
	protected $table = 'roles';
	public $timestamps = false;

	protected $fillable = [
		'nom_role'
	];

	public function droits()
	{
		return $this->hasMany(Droit::class);
	}

	public function utilisateurs()
	{
		return $this->hasMany(User::class);
	}
}