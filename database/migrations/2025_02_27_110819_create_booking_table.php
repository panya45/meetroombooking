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
        Schema::create('booking', function (Blueprint $table) {
            $table->id('book_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('room_id');
            $table->string('booktitle');
            $table->string('bookdetail')->nullable();
            $table->string('username'); // เพิ่ม username
            $table->string('email');
            $table->string('booktel');
            $table->date('book_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('room')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking');
    }
};
