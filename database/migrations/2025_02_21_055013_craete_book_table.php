<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('book', function (Blueprint $table) {
            $table->id('book_id');
            $table->string('room_id');
            $table->string('booktitle');
            $table->string('username');
            $table->string('email');
            $table->string('booktel'); 
            $table->string('bookedate');
            $table->string('booktime');
            $table->string('bookdetail');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
