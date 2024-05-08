<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    // * Added to be able to create Book record inside test functions
    protected $fillable = ['title', 'author'];
}
