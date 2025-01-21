<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangInventaris extends Model
{
    protected $table = "tm_barang_inventaris";

    protected $primaryKey = "br_kode";

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'br_kode',
        'jns_brg_kode',
        'user_id',
        'br_nama',
        'br_foto',
        'br_tgl_terima',
        'br_tgl_entry',
        'br_status'
    ];

    public $timestamp = true;

    public function peminjamanBarang()
    {
        return $this->hasMany(PeminjamanBarang::class, 'br_kode', 'br_kode');
    }
}
