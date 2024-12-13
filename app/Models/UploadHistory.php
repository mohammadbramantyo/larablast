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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
        'user_id',
    ];
}
