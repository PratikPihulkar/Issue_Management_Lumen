<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'company_id', 'name', 'email', 'password', 'role'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    public function assignedProject()
    {
        return $this->belongsToMany(Project::class, 'project_developers', 'developer_id', 'project_id');
    }

    public function issues()
    {
        return $this->hasMany(Issue::class, 'developer_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function getJWTIdentifier() 
	{ 
		return $this->getKey(); 
	} 

	public function getJWTCustomClaims() 
	{ 
		return []; 
	}
}
