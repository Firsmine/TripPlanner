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
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class, 'itinerary_id');
    }
    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }
    public function fields()
    {
        return $this->hasMany(BlockField::class, 'block_id');
    }
}
