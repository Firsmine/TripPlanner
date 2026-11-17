<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function fields()
    {
        return $this->hasMany(TemplateField::class, 'template_id');
    }
    public function itineraryBlock()
    {
        return $this->hasMany(ItineraryBlock::class, 'template_id');
    }
}
