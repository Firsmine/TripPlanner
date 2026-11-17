<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateField extends Model
{
    protected $fillable = [
        'template_id',
        'name',
        'type',
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }
    public function blockField()
    {
        return $this->hasMany(BlockField::class, 'template_id');
    }
}
