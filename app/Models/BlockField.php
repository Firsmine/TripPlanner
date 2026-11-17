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
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function itineraryBlock()
    {
        return $this->belongsTo(ItineraryBlock::class, 'itinerary_block_id');
    }
    public function template()
    {
        return $this->belongsTo(TemplateField::class, 'template_field_id');
    }
}
