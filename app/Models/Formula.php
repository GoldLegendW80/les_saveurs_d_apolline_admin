<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formula extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'price', 'menu_id', 'order'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
