<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }



    public function director()
    {
        return $this->hasOne(Director::class);
    }

    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

    public function advisor()
    {
        return $this->hasOne(Advisor::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }


    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isDirector(): bool
    {
        return $this->role === UserRole::Director;
    }

    public function isAdvisor(): bool
    {
        return $this->role === UserRole::Advisor;
    }

    public function isAgent(): bool
    {
        return $this->role === UserRole::Agent;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }
}
