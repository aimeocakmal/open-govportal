<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $row) {
            Address::query()->updateOrCreate(['label_ms' => $row['label_ms']], $row);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(): array
    {
        return [
            [
                'label_ms' => 'Ibu Pejabat',
                'label_en' => 'Headquarters',
                'address_ms' => "Aras 5, Menara Portal\nJalan Contoh 1\n50000 Bandar Contoh",
                'address_en' => "Level 5, Menara Portal\nJalan Contoh 1\n50000 Bandar Contoh",
                'phone' => '+603-0000 1000',
                'fax' => '+603-0000 1001',
                'email' => 'hello@opengovportal.example',
                'google_maps_url' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'label_ms' => 'Pusat Sokongan',
                'label_en' => 'Support Centre',
                'address_ms' => "Blok B, Kompleks Contoh\nJalan Contoh 2\n50100 Bandar Contoh",
                'address_en' => "Block B, Kompleks Contoh\nJalan Contoh 2\n50100 Bandar Contoh",
                'phone' => '+603-0000 2000',
                'fax' => null,
                'email' => 'sokongan@opengovportal.example',
                'google_maps_url' => null,
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];
    }
}
