<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('td_peminjaman_barang', function (Blueprint $table) {
            $table->string('pbd_id', 20)->primary();
            $table->string('pb_id', 20);
            $table->foreign('pb_id')->references('pb_id')->on("tm_peminjaman");
            $table->string('br_kode', 12);
            $table->foreign('br_kode')->references('br_kode')->on('tm_barang_inventaris');
            $table->dateTime('pdb_tgl');
            $table->string('pdb_sts', 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('td_peminjamana_barang');
    }
};
