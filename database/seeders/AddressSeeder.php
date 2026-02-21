<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        Address::create([
            'label_ms' => 'Ibu Pejabat',
            'label_en' => 'Headquarters',
            'address_ms' => "Aras 13, Menara MITI\nNo. 7, Jalan Sultan Haji Ahmad Shah\n50480 Kuala Lumpur",
            'address_en' => "Level 13, MITI Tower\nNo. 7, Jalan Sultan Haji Ahmad Shah\n50480 Kuala Lumpur",
            'phone' => '+603-8000 8000',
            'fax' => '+603-8000 8001',
            'email' => 'info@digital.gov.my',
            'google_maps_url' => 'https://maps.google.com/?q=Menara+MITI+Kuala+Lumpur',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Address::create([
            'label_ms' => 'Pejabat Putrajaya',
            'label_en' => 'Putrajaya Office',
            'address_ms' => "Blok C2, Parcel C\nPusat Pentadbiran Kerajaan Persekutuan\n62000 Putrajaya",
            'address_en' => "Block C2, Parcel C\nFederal Government Administrative Centre\n62000 Putrajaya",
            'phone' => '+603-8872 3000',
            'email' => 'putrajaya@digital.gov.my',
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }
}
