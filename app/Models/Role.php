<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
    protected $fillable = [
        'name',
    ];




    use HasFactory;

    const ADMIN = 1;
    const USER = 2;

    public function users()
    {
        return $this->hasMany(User::class);
    }
   
}
