<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    // === ДОБАВЬТЕ ЭТУ СТРОКУ ===
    protected $fillable = ['name', 'age'];
}
