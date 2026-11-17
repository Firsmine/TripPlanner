<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItineraryBlock extends Model
{
    protected $fillable = [
        'itinerary_id',
        'template_id',
        'position',
    ];
    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class, 'itinerary_id');
    }
    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }
    public function blockField()
    {
        return $this->hasMany(BlockField::class, 'block_id');
    }
}
