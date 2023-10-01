<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string,string,string,string,int>
     */
    protected $fillable = [
        'user_id',
        'last_name',
        'first_name',
        'middle_name',
        'phone',
        'avatar'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
