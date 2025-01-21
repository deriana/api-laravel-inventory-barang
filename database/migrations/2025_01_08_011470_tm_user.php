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
        Schema::create('tm_user', function (Blueprint $table) {
            $table->string('user_id', 10)->primary();
            $table->string('user_nama', 50);   
            $table->string('user_email', 100)->unique();
            $table->string('user_pass', 255);   
            $table->string('user_hak', 2);   
            $table->string('user_sts', 2);   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tm_user');
    }
};
