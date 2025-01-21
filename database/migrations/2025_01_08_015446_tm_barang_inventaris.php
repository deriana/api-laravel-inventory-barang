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
        Schema::create('tm_barang_inventaris', function(Blueprint $table) {
            $table->string('br_kode', 12)->primary();
            $table->string("jns_brg_kode", 5);
            $table->foreign("jns_brg_kode")->references("jns_brg_kode")->on("tr_jenis_barang");
            $table->string("user_id", 10);
            $table->foreign("user_id")->references("user_id")->on("tm_user");
            $table->string("br_nama", 50);
            $table->string("br_foto", 100);
            $table->date("br_tgl_terima");
            $table->dateTime("br_tgl_entry");
            $table->string("br_status", 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('tm_barang_inventaris');
    }
};
