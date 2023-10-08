<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\File\Models\File;

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
        'phone'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function avatar()
    {
        return $this->morphOne(File::class, 'fileable');
    }
}
