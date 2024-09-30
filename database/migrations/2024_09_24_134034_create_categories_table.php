<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
