<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterData extends Model
{
    //
    use HasFactory;

    /**
     * @var string
     *  */ 
    protected $table = 'master_data';

    /**
     * 
     * Attributes
     * 
     * @var array
     */
    protected $fillable = [
        'nama',
        'dob',
        'alamat_rumah',
        'kec_rmh',
        'kota_rmh',
        'perusahaan',
        'jabatan',
        'alamat_perush',
        'kota_perush',
        'kode_pos',
        'telp_rumah',
        'telp_kantor',
        'hp_2',
        'hp_utama',
    ];
}
