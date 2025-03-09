<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('replies', function (Blueprint $table) {
            $table->id();  // รหัสการตอบกลับ
            $table->foreignId('comment_id')->constrained('comments')->onDelete('cascade');  // เชื่อมโยงกับตาราง comments
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');  // เชื่อมโยงกับตาราง users
            $table->text('reply');  // เนื้อหาการตอบกลับ
            $table->timestamps();  // สร้าง created_at และ updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('replies');
    }
}
