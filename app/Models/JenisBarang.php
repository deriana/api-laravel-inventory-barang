<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisBarang extends Model
{
    protected $table = "tr_jenis_barang";

    protected $primaryKey = "jns_brg_kode";

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'jns_brg_kode',
        'jns_brg_nama',
    ];

    public $timestamp = true;
}
