<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDeveloper extends Model
{
    protected $fillable = ['company_id', 'project_id', 'developer_id', 'project_role'];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id', 'company_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
