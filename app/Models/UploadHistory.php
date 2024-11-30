<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadHistory extends Model
{
    //
    use HasFactory;

    /**
     * @var string
     *  */
    protected $table = 'upload_history';

    /**
     * 
     * Attributes
     * 
     * @var array
     */
    protected $fillable = [
        'saved_rows',
        'processed_rows',
        'duplicate_rows',
        'valid_rows',
    ];
}
