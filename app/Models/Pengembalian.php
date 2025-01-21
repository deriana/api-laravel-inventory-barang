<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    protected $table = 'tm_pengembalian';  

    protected $primaryKey = 'kembali_id';

    public $incrementing = false; 

    protected $keyType = 'string';

    protected $fillable = [
        'kembali_id',
        'pb_id',
        'user_id',
        'kembali_tgl',
        'kembali_sts', 
    ];

    public $timestamps = true;

    public function peminjamanBarang()
    {
        return $this->belongsTo(PeminjamanBarang::class, 'br_kode', 'br_kode');
    }

    function barangInventaris()
    {
        return $this->belongsTo(barangInventaris::class, 'br_kode', 'br_kode');
    }
}
