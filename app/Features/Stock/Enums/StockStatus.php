<?php

namespace App\Features\Stock\Enums;

enum StockStatus: string
{
    case AVAILABLE = 'available';
    case LOW_STOCK = 'low_stock';
    case OUT_OF_STOCK = 'out_of_stock';

    /**
     * Human readable label shown in the UI (always paired with the colour badge).
     */
    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Tersedia',
            self::LOW_STOCK => 'Stok Menipis',
            self::OUT_OF_STOCK => 'Habis',
        };
    }

    /**
     * Tailwind classes for the status badge (hijau / kuning / merah).
     * Warna gelap di atas latar terang menjaga kontras teks >= 4.5:1.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::AVAILABLE => 'bg-emerald-100 text-emerald-900 border-emerald-700',
            self::LOW_STOCK => 'bg-amber-100 text-amber-900 border-amber-700',
            self::OUT_OF_STOCK => 'bg-rose-100 text-rose-900 border-rose-700',
        };
    }

    /**
     * Determine the status from the quantity compared to the minimum stock threshold.
     */
    public static function fromQuantity(int $quantity, int $minStock): self
    {
        if ($quantity <= 0) {
            return self::OUT_OF_STOCK;
        }

        if ($quantity <= $minStock) {
            return self::LOW_STOCK;
        }

        return self::AVAILABLE;
    }
}
