<?php

namespace Database\Seeders;

use App\Models\Broadcast;
use Illuminate\Database\Seeder;

class BroadcastSeeder extends Seeder
{
    public function run(): void
    {
        Broadcast::factory()->create([
            'title_ms' => 'Kementerian Digital lancar inisiatif baharu',
            'title_en' => 'Ministry of Digital launches new initiative',
            'slug' => 'kementerian-digital-lancar-inisiatif-baharu',
            'content_ms' => 'Kementerian Digital Malaysia telah melancarkan inisiatif baharu untuk mempercepat transformasi digital negara.',
            'content_en' => 'The Ministry of Digital Malaysia has launched a new initiative to accelerate the nation\'s digital transformation.',
            'excerpt_ms' => 'Inisiatif baharu untuk transformasi digital.',
            'excerpt_en' => 'New initiative for digital transformation.',
            'type' => 'announcement',
            'status' => 'draft',
        ]);

        Broadcast::factory()->published()->create([
            'title_ms' => 'Pelan Tindakan Ekonomi Digital Malaysia 2030',
            'title_en' => 'Malaysia Digital Economy Action Plan 2030',
            'slug' => 'pelan-tindakan-ekonomi-digital-malaysia-2030',
            'content_ms' => 'Kerajaan telah mengumumkan Pelan Tindakan Ekonomi Digital Malaysia 2030 yang bertujuan menjadikan Malaysia hab digital serantau.',
            'content_en' => 'The government has announced the Malaysia Digital Economy Action Plan 2030, aimed at making Malaysia a regional digital hub.',
            'excerpt_ms' => 'Pelan strategik ekonomi digital negara.',
            'excerpt_en' => 'National digital economy strategic plan.',
            'type' => 'press_release',
        ]);

        Broadcast::factory()->published()->create([
            'title_ms' => 'Kemaskini MyDigital ID kini tersedia',
            'title_en' => 'MyDigital ID update now available',
            'slug' => 'kemaskini-mydigital-id-kini-tersedia',
            'content_ms' => 'Versi terkini MyDigital ID kini boleh dimuat turun melalui App Store dan Google Play.',
            'content_en' => 'The latest version of MyDigital ID is now available for download on the App Store and Google Play.',
            'excerpt_ms' => 'Kemaskini aplikasi MyDigital ID.',
            'excerpt_en' => 'MyDigital ID application update.',
            'type' => 'news',
        ]);
    }
}
