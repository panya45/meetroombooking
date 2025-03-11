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
        Schema::create('room', function (Blueprint $table) {
            $table->id();  // ใช้เพื่อสร้าง primary key และ auto increment
            $table->string('room_name');
            $table->string('room_detail');
            $table->string('room_status')->default('available');
            $table->string('room_pic')->nullable();
            $table->timestamps();
        });

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room');
    }
};
