<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockField extends Model
{
    protected $fillable = [
        'itinerary_block_id',
        'template_field_id',
        'value'
    ];
    public function itineraryBlock()
    {
        return $this->belongsTo(ItineraryBlock::class, 'itinerary_block_id');
    }
    public function templateField()
    {
        return $this->belongsTo(TemplateField::class, 'template_field_id');
    }
}
