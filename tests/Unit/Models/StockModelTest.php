<?php

namespace Tests\Unit\Models;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $stock->user);
        $this->assertEquals($user->id, $stock->user->id);
    }

    public function test_stock_casts_numeric_fields(): void
    {
        $stock = Stock::factory()->create(['quantity' => '12', 'min_stock' => '4', 'price' => 1500]);
        $stock = $stock->fresh();

        $this->assertSame(12, $stock->quantity);
        $this->assertSame(4, $stock->min_stock);
        $this->assertSame('1500.00', $stock->price);
    }

    public function test_stock_factory_states(): void
    {
        $low = Stock::factory()->lowStock()->create();
        $this->assertSame('low_stock', $low->status);
        $this->assertSame(StockStatus::LOW_STOCK, $low->statusEnum());

        $out = Stock::factory()->outOfStock()->create();
        $this->assertSame('out_of_stock', $out->status);
        $this->assertSame(StockStatus::OUT_OF_STOCK, $out->statusEnum());
    }

    public function test_status_enum_falls_back_to_quantity_for_unknown_status(): void
    {
        $stock = new Stock(['quantity' => 2, 'min_stock' => 5, 'status' => 'weird']);
        $this->assertSame(StockStatus::LOW_STOCK, $stock->statusEnum());
    }

    public function test_cover_image_url_returns_correct_url_or_null(): void
    {
        $stockWithoutCover = Stock::factory()->create(['cover_image_path' => null]);
        $this->assertNull($stockWithoutCover->coverImageUrl());

        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('stock-covers/sample.png', 'content');

        $stockWithCover = Stock::factory()->create(['cover_image_path' => 'stock-covers/sample.png']);
        $this->assertNotNull($stockWithCover->coverImageUrl());
        $this->assertStringContainsString('stock-covers/sample.png', $stockWithCover->coverImageUrl());
    }
}

