<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();  // รหัสคอมเมนต์
            $table->unsignedBigInteger('book_id');  // ใช้ unsignedBigInteger แทน foreignId
            $table->unsignedBigInteger('user_id');  // ใช้ unsignedBigInteger แทน foreignId
            $table->text('comment');  // เนื้อหาคอมเมนต์
            $table->unsignedBigInteger('parent_id')->nullable();  // การตอบกลับ (ใช้ unsignedBigInteger แทน foreignId)
            $table->timestamps();  // สร้าง created_at และ updated_at

            // การเชื่อมโยง foreign key
            $table->foreign('book_id')->references('book_id')->on('booking')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
