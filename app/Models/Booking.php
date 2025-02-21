<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    // Specify the table name if it's not the plural form of the model name.
    protected $table = 'booking';

    // Set the primary key if it is not "id"
    protected $primaryKey = 'book_id';

    // Allow mass assignment on the following fields
    protected $fillable = [
        'room_id', 'booktitle', 'username', 'email', 'booktel', 'bookedate', 'booktime', 'bookdetail'
    ];
}
