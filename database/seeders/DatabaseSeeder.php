<?php

namespace Database\Seeders;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ]
        );

        $member = User::firstOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => 'Member User',
                'username' => 'member',
                'password' => Hash::make('member'),
                'role' => 'member',
            ]
        );

        $samples = [
            ['name' => 'Kertas HVS A4 70gsm', 'sku' => 'ATK-001', 'unit' => 'rim', 'quantity' => 40, 'min_stock' => 10, 'price' => 52000, 'description' => "Kertas **HVS A4** untuk kebutuhan cetak harian.\n\n- Ukuran: A4\n- Isi: 500 lembar per rim"],
            ['name' => 'Pulpen Gel Hitam 0.5mm', 'sku' => 'ATK-002', 'unit' => 'pcs', 'quantity' => 8, 'min_stock' => 10, 'price' => 4500, 'description' => 'Pulpen gel tinta hitam, **segera restok** karena mendekati batas minimum.'],
            ['name' => 'Spidol Whiteboard Biru', 'sku' => 'ATK-003', 'unit' => 'pcs', 'quantity' => 0, 'min_stock' => 5, 'price' => 9000, 'description' => 'Stok habis, menunggu pengiriman dari pemasok.'],
            ['name' => 'Map Plastik Kancing', 'sku' => 'ATK-004', 'unit' => 'pcs', 'quantity' => 120, 'min_stock' => 20, 'price' => 3500, 'description' => null],
            ['name' => 'Tinta Printer Hitam 003', 'sku' => 'PRT-001', 'unit' => 'botol', 'quantity' => 5, 'min_stock' => 5, 'price' => 85000, 'description' => 'Isi ulang untuk printer tangki tinta.'],
        ];

        foreach ($samples as $sample) {
            Stock::firstOrCreate(
                ['user_id' => $member->id, 'name' => $sample['name']],
                $sample + ['status' => StockStatus::fromQuantity($sample['quantity'], $sample['min_stock'])->value]
            );
        }
    }
}
