<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Stmt\Block;

class Itinerary extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'summary',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function blocks()
    {
        return $this->hasMany(ItineraryBlock::class, 'itinerary_id');
    }
}
