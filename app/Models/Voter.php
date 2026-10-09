<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voter extends Model
{
    protected $fillable = [
        'nik', 'name', 'rt', 'rw',
        'is_voted', 'candidate_id', 'ticket_code', 'voted_at',
    ];

    protected $casts = [
        'is_voted' => 'boolean',
        'voted_at' => 'datetime',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
