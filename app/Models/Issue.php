<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $fillable=['company_id','project_id', 'developer_id', 'title', 'description', 'status', 'priority', 'deadline'];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id', 'company_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function developer()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
