<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('formulas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('formulas');
    }
};