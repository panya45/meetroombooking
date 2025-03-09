<?php

// app/Models/Reply.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Reply extends Model
{
    use HasFactory;

    protected $fillable = ['comment_id', 'user_id', 'reply'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
