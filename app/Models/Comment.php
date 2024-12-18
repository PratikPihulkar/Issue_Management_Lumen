<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable=['company_id', 'issue_id', 'user_id', 'comment'];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id', 'company_id');
    }

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
