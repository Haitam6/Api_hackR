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
class User extends Model implements JWTSubject // Implement JWTSubject
{
    use Notifiable; // Use Notifiable if you are sending notifications

    protected $table = 'users';

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
    ];

    public function logs()
    {
        return $this->hasMany(Log::class, 'id_user');
    }

    // Implement the methods required by the JWTSubject interface
    public function getJWTIdentifier()
    {
        return $this->getKey(); // This returns the user's primary key (id)
    }

    public function getJWTCustomClaims()
    {
        return []; // You can add any additional claims here if needed
    }
}
