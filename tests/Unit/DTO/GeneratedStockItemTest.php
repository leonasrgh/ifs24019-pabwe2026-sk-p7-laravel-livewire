<?php

namespace Tests\Unit\DTO;

use App\Features\Ai\DTO\GeneratedStockItem;
use PHPUnit\Framework\TestCase;

class GeneratedStockItemTest extends TestCase
{
    public function test_dto_properties_and_to_array(): void
    {
        $dto = new GeneratedStockItem(
            name: 'Kertas A4',
            sku: 'ATK-001',
            description: 'Kertas HVS 80 gram',
            quantity: 40,
            unit: 'rim',
            price: 52000.5,
            minStock: 10,
        );

        $this->assertSame('Kertas A4', $dto->name);
        $this->assertSame('ATK-001', $dto->sku);
        $this->assertSame(40, $dto->quantity);
        $this->assertSame('rim', $dto->unit);
        $this->assertSame(10, $dto->minStock);

        $this->assertEquals([
            'name' => 'Kertas A4',
            'sku' => 'ATK-001',
            'description' => 'Kertas HVS 80 gram',
            'quantity' => 40,
            'unit' => 'rim',
            'price' => 52000.5,
            'min_stock' => 10,
        ], $dto->toArray());
    }

    public function test_dto_defaults(): void
    {
        $dto = new GeneratedStockItem(name: 'Pulpen');

        $this->assertNull($dto->sku);
        $this->assertSame(0, $dto->quantity);
        $this->assertSame('pcs', $dto->unit);
        $this->assertSame(5, $dto->minStock);
    }
}
