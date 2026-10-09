<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $fillable = ['number', 'name', 'vision', 'photo', 'color'];

    public function voters()
    {
        return $this->hasMany(Voter::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
