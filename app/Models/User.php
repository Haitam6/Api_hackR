<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject; // Add this line
use Illuminate\Notifications\Notifiable; // Add this line if you're using notifications
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Log[] $logs
 *
 * @package App\Models
 */
class User extends Authenticatable implements JWTSubject
{
    protected $table = 'users';  // The name of your table
    protected $primaryKey = 'id'; // The primary key of your table
    public $timestamps = false;  // Disable automatic timestamps if not needed

    protected $casts = [
        'role_id' => 'int'  // Assuming `role_id` is an integer
    ];

    protected $fillable = [
        'nom',               // Assuming 'nom' is for user's name
        'email',
        'role_id',
        'motDePasse',        // Assuming 'motDePasse' is for user's password
        'statut',            // Assuming 'statut' refers to a status field
        'dateCreation'       // Assuming 'dateCreation' is a field for creation date
    ];

    // Implement the methods required by the JWTSubject interface
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Return the user's primary key (id)
    }

    public function getJWTCustomClaims()
    {
        return []; // Add any custom claims here if needed
    }

    public function getAuthPassword()
    {
        return $this->motDePasse; // This maps to the 'motDePasse' for password authentication
    }

    public function role()
    {
        return $this->belongsTo(Role::class); // Assuming there's a Role model linked with the user
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'id_user'); // Assuming the `Log` model is related to `User`
    }
}