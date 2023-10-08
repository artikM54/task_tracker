<?php

namespace Modules\File\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'path',
        'fileable_id',
        'fileable_type'
    ];

    protected static function newFactory()
    {
        return \Modules\File\Database\factories\FileFactory::new();
    }
}
