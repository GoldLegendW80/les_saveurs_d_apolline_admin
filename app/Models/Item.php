<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'description', 'price', 'category_id', 'order'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
