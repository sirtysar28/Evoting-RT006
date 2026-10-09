<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Candidate;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Akun Admin Panitia ----
        Admin::updateOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'Panitia RT 006',
                'password' => bcrypt('admin123'),
            ]
        );

        // ---- Calon Ketua RT (data dari PILRT2026.html) ----
        Candidate::updateOrCreate(
            ['number' => 1],
            [
                'name'    => 'Calon Ketua RT 01',
                'vision'  => 'Mewujudkan RT 006 yang Aman, Harmonis, dan Transparan',
                'photo'   => null,
                'color'   => '#0ea5e9',
            ]
        );

        Candidate::updateOrCreate(
            ['number' => 2],
            [
                'name'    => 'Calon Ketua RT 02',
                'vision'  => 'Pelayanan Warga Cepat, Lingkungan Bersih dan Rapi',
                'photo'   => null,
                'color'   => '#8b5cf6',
            ]
        );
    }
}
