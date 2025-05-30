<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReference extends Model
{
    use HasFactory;
    protected $table = "user_references";
    protected $guarded = ['id'];

    function user(){
       return $this->belongsTo(User::class);
    }

}
