<?php

namespace Database\Seeders;

use App\Models\PolicyFile;
use Illuminate\Database\Seeder;

class PolicyFileSeeder extends Seeder
{
    public function run(): void
    {
        PolicyFile::factory()->create([
            'title_ms' => 'Pekeliling Am Bilangan 1 Tahun 2024',
            'title_en' => 'General Circular No. 1 of 2024',
            'filename' => 'pekeliling-am-1-2024.pdf',
            'file_url' => 'files/pekeliling-am-1-2024.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1048576,
            'category' => 'pekeliling',
            'is_public' => true,
        ]);

        PolicyFile::factory()->create([
            'title_ms' => 'Garis Panduan Keselamatan Siber',
            'title_en' => 'Cyber Security Guidelines',
            'filename' => 'garis-panduan-keselamatan-siber.pdf',
            'file_url' => 'files/garis-panduan-keselamatan-siber.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2097152,
            'category' => 'garis_panduan',
            'is_public' => true,
        ]);

        PolicyFile::factory()->private()->create([
            'title_ms' => 'Laporan Dalaman Q4 2024',
            'title_en' => 'Internal Report Q4 2024',
            'filename' => 'laporan-dalaman-q4-2024.pdf',
            'file_url' => 'files/laporan-dalaman-q4-2024.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 524288,
            'category' => 'laporan',
        ]);
    }
}
