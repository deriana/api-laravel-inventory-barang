<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TmUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create(
            [
                'user_id' => 'SUI26',
                'user_nama' => 'Hoshimachi Suisei',
                'user_email' => 'suisei@example.com',
                'user_pass' => Hash::make('suisei123'),
                'user_hak' => 'Ad',
                'user_sts' => 'Y'
            ]
        );
    }
}
