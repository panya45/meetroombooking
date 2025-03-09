<?php

// app/Models/Comment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reply;
class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'book_id', 'comment', 'parent_id'];

    public function replies()
    {
        return $this->hasMany(Reply::class, 'comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
