<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    protected $table = 'items';

    protected $fillable = ['creatorId', 'name', 'created_at', 'updated_at', 'mondayId','creatorName'];
    public $timestamps = false;

}
