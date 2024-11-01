<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'description', 'order'];

    /**
     * Get the categories for the menu.
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function formulas()
    {
        return $this->hasMany(Formula::class);
    }
}
