<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['company_id','manager_id', 'name', 'description', 'start_date', 'end_date'];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id', 'company_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function developers()
    {
        return $this->belongsToMany(User::class, 'project_developers', 'project_id', 'developer_id');
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }
}
