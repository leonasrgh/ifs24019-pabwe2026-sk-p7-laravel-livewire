<?php

namespace Tests\Feature\Stock;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class StockListLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_stocks(): void
    {
        $this->get('/stocks')->assertRedirect(route('login'));
    }

    public function test_member_can_view_own_stocks(): void
    {
        $user = User::factory()->member()->create();
        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Barang Spesifik Member']);

        $otherUser = User::factory()->member()->create();
        Stock::factory()->create(['user_id' => $otherUser->id, 'name' => 'Barang Rahasia Orang Lain']);

        $this->actingAs($user);

        Livewire::test(StockList::class)
            ->assertSee('Barang Spesifik Member')
            ->assertDontSee('Barang Rahasia Orang Lain');
    }

    public function test_create_stock_validation_and_success(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        Livewire::test(StockList::class)
            ->call('openCreateModal')
            ->set('formName', '')
            ->set('formQuantity', '-1')
            ->set('formUnit', '')
            ->set('formPrice', 'abc')
            ->set('formMinStock', 'x')
            ->call('saveStock')
            ->assertHasErrors(['formName', 'formQuantity', 'formUnit', 'formPrice', 'formMinStock']);

        Livewire::test(StockList::class)
            ->call('openCreateModal')
            ->set('formName', 'Kertas A4')
            ->set('formSku', 'ATK-001')
            ->set('formDescription', '**Kertas** HVS')
            ->set('formQuantity', '3')
            ->set('formUnit', 'rim')
            ->set('formPrice', '52000')
            ->set('formMinStock', '5')
            ->call('saveStock')
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false);

        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Kertas A4',
            'sku' => 'ATK-001',
            'quantity' => 3,
            'unit' => 'rim',
            'min_stock' => 5,
            'status' => 'low_stock',
        ]);
    }

    public function test_create_stock_with_optional_fields_empty(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        Livewire::test(StockList::class)
            ->call('openCreateModal')
            ->set('formName', 'Pulpen')
            ->call('saveStock')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stocks', [
            'name' => 'Pulpen',
            'sku' => null,
            'price' => null,
            'quantity' => 0,
            'status' => 'out_of_stock',
        ]);
    }

    public function test_edit_stock_success(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $stock = Stock::factory()->create(['user_id' => $user->id, 'name' => 'Nama Lama', 'price' => 1500, 'sku' => 'OLD-1']);

        Livewire::test(StockList::class)
            ->call('openEditModal', $stock->id)
            ->assertSet('editingStockId', $stock->id)
            ->assertSet('formName', 'Nama Lama')
            ->assertSet('formSku', 'OLD-1')
            ->set('formName', 'Nama Telah Diedit')
            ->set('formQuantity', '0')
            ->call('saveStock')
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false);

        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'name' => 'Nama Telah Diedit',
            'quantity' => 0,
            'status' => 'out_of_stock',
        ]);
    }

    public function test_edit_stock_of_other_user_is_forbidden(): void
    {
        $user = User::factory()->member()->create();
        $other = Stock::factory()->create();

        $this->actingAs($user);

        Livewire::test(StockList::class)
            ->call('openEditModal', $other->id)
            ->assertForbidden();
    }

    public function test_adjust_quantity_updates_status(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $stock = Stock::factory()->create(['user_id' => $user->id, 'quantity' => 6, 'min_stock' => 5, 'status' => 'available']);

        Livewire::test(StockList::class)
            ->call('adjustQuantity', $stock->id, -1);
        $this->assertSame(5, $stock->fresh()->quantity);
        $this->assertSame('low_stock', $stock->fresh()->status);

        Livewire::test(StockList::class)
            ->call('adjustQuantity', $stock->id, -50);
        $this->assertSame(0, $stock->fresh()->quantity);
        $this->assertSame('out_of_stock', $stock->fresh()->status);

        Livewire::test(StockList::class)
            ->call('adjustQuantity', $stock->id, 5000);
        $this->assertSame(1000, $stock->fresh()->quantity);
        $this->assertSame('available', $stock->fresh()->status);
    }

    public function test_adjust_quantity_of_other_user_is_forbidden(): void
    {
        $user = User::factory()->member()->create();
        $other = Stock::factory()->create(['quantity' => 10]);

        $this->actingAs($user);

        Livewire::test(StockList::class)
            ->call('adjustQuantity', $other->id, 1)
            ->assertForbidden();

        $this->assertSame(10, $other->fresh()->quantity);
    }

    public function test_delete_stock(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $stock = Stock::factory()->create(['user_id' => $user->id]);

        Livewire::test(StockList::class)
            ->call('deleteStock', $stock->id);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_confirm_and_cancel_delete_stock(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $stock = Stock::factory()->create(['user_id' => $user->id]);

        Livewire::test(StockList::class)
            ->assertSet('confirmingStockDeletionId', null)
            ->call('confirmDelete', $stock->id)
            ->assertSet('confirmingStockDeletionId', $stock->id)
            ->call('cancelDelete')
            ->assertSet('confirmingStockDeletionId', null)
            ->call('confirmDelete', $stock->id)
            ->call('deleteStock')
            ->assertSet('confirmingStockDeletionId', null);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_search_and_status_filter(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Makan Siang', 'status' => 'out_of_stock', 'quantity' => 0]);
        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Makan Malam', 'sku' => 'MLM-9', 'status' => 'available']);

        Livewire::test(StockList::class)
            ->set('search', 'Siang')
            ->assertSee('Makan Siang')
            ->assertDontSee('Makan Malam')
            ->set('search', 'MLM-9')
            ->assertSee('Makan Malam')
            ->assertDontSee('Makan Siang')
            ->set('search', '')
            ->set('statusFilter', 'available')
            ->assertSee('Makan Malam')
            ->assertDontSee('Makan Siang');
    }

    public function test_export_excel(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Exportable Stock']);

        Livewire::test(StockList::class)
            ->call('exportExcel')
            ->assertFileDownloaded();
    }

    public function test_import_excel(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach (['No', 'Name', 'SKU', 'Description', 'Quantity', 'Unit', 'Price', 'Min Stock'] as $i => $h) {
            $sheet->setCellValue(chr(65 + $i).'1', $h);
        }
        foreach (['1', 'Imported Excel Stock', 'IMP-1', 'Excel Desc', '2', 'box', '10000', '5'] as $i => $v) {
            $sheet->setCellValue(chr(65 + $i).'2', $v);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'test_upload_').'.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);
        $fileContent = file_get_contents($tempPath);
        @unlink($tempPath);

        $uploadedFile = UploadedFile::fake()->createWithContent('stocks.xlsx', $fileContent);

        Livewire::test(StockList::class)
            ->call('openImportModal')
            ->assertSet('showImportModal', true)
            ->set('importFile', $uploadedFile)
            ->call('importExcel')
            ->assertSet('showImportModal', false);

        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Imported Excel Stock',
            'sku' => 'IMP-1',
            'quantity' => 2,
            'status' => 'low_stock',
        ]);
    }

    public function test_import_excel_requires_file(): void
    {
        $this->actingAs(User::factory()->member()->create());

        Livewire::test(StockList::class)
            ->call('openImportModal')
            ->call('importExcel')
            ->assertHasErrors(['importFile']);
    }

    public function test_ai_generation_and_saving(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $mockAiClient = $this->createStub(AiClientInterface::class);
        $mockAiClient->method('generateStocks')->willReturn([
            new GeneratedStockItem(name: 'AI Item 1', sku: 'AI-1', description: 'Desc 1', quantity: 12, unit: 'box', price: 5000, minStock: 4),
            new GeneratedStockItem(name: 'AI Item 2'),
        ]);
        $this->app->instance(AiClientInterface::class, $mockAiClient);

        Livewire::test(StockList::class)
            ->call('openAiModal')
            ->set('aiPrompt', '')
            ->set('aiCount', 0)
            ->call('generateWithAi')
            ->assertHasErrors(['aiPrompt', 'aiCount']);

        $component = Livewire::test(StockList::class)
            ->call('openAiModal')
            ->assertSet('showAiModal', true)
            ->set('aiPrompt', 'Buatkan stok alat tulis')
            ->set('aiCount', 2)
            ->call('generateWithAi')
            ->assertCount('aiGeneratedItems', 2)
            ->assertCount('aiSelectedIndices', 2);

        $component->set('aiSelectedIndices', [])
            ->call('saveAiGenerated')
            ->assertSet('aiErrorMessage', 'Pilih minimal satu item stok yang ingin disimpan.');

        $component->set('aiSelectedIndices', [0, 1, 7])
            ->call('saveAiGenerated')
            ->assertSet('showAiModal', false);

        $this->assertDatabaseHas('stocks', ['user_id' => $user->id, 'name' => 'AI Item 1', 'quantity' => 12, 'status' => 'available']);
        $this->assertDatabaseHas('stocks', ['user_id' => $user->id, 'name' => 'AI Item 2', 'status' => 'out_of_stock']);
        $this->assertDatabaseCount('stocks', 2);
    }

    public function test_ai_generation_handles_exception(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        $mockAiClient = $this->createStub(AiClientInterface::class);
        $mockAiClient->method('generateStocks')->willThrowException(new \RuntimeException('API quota exceeded'));
        $this->app->instance(AiClientInterface::class, $mockAiClient);

        Livewire::test(StockList::class)
            ->call('openAiModal')
            ->set('aiPrompt', 'Error test')
            ->call('generateWithAi')
            ->assertSet('aiErrorMessage', 'API quota exceeded');
    }

    public function test_close_modals(): void
    {
        $user = User::factory()->member()->create();
        $this->actingAs($user);

        Livewire::test(StockList::class)
            ->call('openCreateModal')
            ->call('closeFormModal')
            ->assertSet('showFormModal', false)
            ->call('openImportModal')
            ->call('closeImportModal')
            ->assertSet('showImportModal', false)
            ->call('openAiModal')
            ->call('closeAiModal')
            ->assertSet('showAiModal', false);
    }
}
