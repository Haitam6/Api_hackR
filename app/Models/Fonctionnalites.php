<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Fonctionnalite",
 *     type="object",
 *     required={"nom_fonctionnalite"},
 *     @OA\Property(property="id", type="integer", description="ID de la fonctionnalité"),
 *     @OA\Property(property="nom_fonctionnalite", type="string", description="Nom de la fonctionnalité"),
 *     @OA\Property(property="description", type="string", description="Description de la fonctionnalité")
 * )
 */
class Fonctionnalites extends Model
{
    protected $table = 'fonctionnalites';
    public $timestamps = false;

    protected $fillable = [
        'nom_fonctionnalite',
        'description'
    ];

    public function droits()
    {
        return $this->hasMany(Droit::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }
}
