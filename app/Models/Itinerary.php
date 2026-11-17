<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Stmt\Block;

class Itinerary extends Model
{
    protected $fillable = [
        'title',
        'summary',
        'slug',
        'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function block()
    {
        return $this->hasMany(Block::class, 'itinerary_id');
    }
}
