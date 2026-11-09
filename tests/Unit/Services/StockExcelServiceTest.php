<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Features\Stock\Services\StockExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class StockExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockExcelService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockExcelService(new StockRepository());
    }

    public function test_build_spreadsheet_and_export_response(): void
    {
        $user = User::factory()->create();
        $stocks = Stock::factory()->count(2)->create(['user_id' => $user->id]);

        $spreadsheet = $this->service->buildSpreadsheet($stocks, 'Custom Stocks');
        $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
        $this->assertEquals('Custom Stocks', $spreadsheet->getActiveSheet()->getTitle());

        $response = $this->service->exportToResponse($stocks, 'test.xlsx');
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        ob_start();
        $response->sendContent();
        $outputContent = ob_get_clean();
        $this->assertNotEmpty($outputContent);
    }

    public function test_import_spreadsheet(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (['No', 'Name', 'SKU', 'Description', 'Quantity', 'Unit', 'Price', 'Min Stock'] as $i => $h) {
            $sheet->setCellValue(chr(65 + $i).'1', $h);
        }

        // Baris 2: data lengkap, stok tersedia
        foreach (['1', 'Kertas A4', 'ATK-001', 'Kertas HVS', '40', 'rim', '52000.5', '10'] as $i => $v) {
            $sheet->setCellValue(chr(65 + $i).'2', $v);
        }

        // Baris 3: stok menipis, kolom opsional kosong / "-", angka tidak valid memakai default
        foreach (['2', 'Pulpen', '-', '-', '3', '', 'abc', 'x'] as $i => $v) {
            $sheet->setCellValue(chr(65 + $i).'3', $v);
        }

        // Baris 4: quantity 0 -> habis, nilai negatif dibulatkan ke 0
        foreach (['3', 'Spidol', 'ATK-003', 'Merah', '0', 'pcs', '-5', '-2'] as $i => $v) {
            $sheet->setCellValue(chr(65 + $i).'4', $v);
        }

        // Baris 5: nama kosong (diabaikan)
        $sheet->setCellValue('A5', '4');
        $sheet->setCellValue('B5', '');

        // Baris 6: header duplikat (diabaikan)
        $sheet->setCellValue('A6', 'No');
        $sheet->setCellValue('B6', 'Name');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_').'.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $result = $this->service->import($tempPath, $user->id);
        @unlink($tempPath);

        $this->assertSame(3, $result['imported']);
        $this->assertSame([], $result['errors']);

        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Kertas A4',
            'sku' => 'ATK-001',
            'quantity' => 40,
            'unit' => 'rim',
            'min_stock' => 10,
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Pulpen',
            'sku' => null,
            'description' => null,
            'quantity' => 3,
            'unit' => 'pcs',
            'price' => null,
            'min_stock' => 5,
            'status' => 'low_stock',
        ]);
        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Spidol',
            'quantity' => 0,
            'min_stock' => 0,
            'status' => 'out_of_stock',
        ]);
    }

    public function test_import_with_only_header_inserts_nothing(): void
    {
        $user = User::factory()->create();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->setCellValue('B1', 'Name');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_').'.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $result = $this->service->import($tempPath, $user->id);
        @unlink($tempPath);

        $this->assertSame(0, $result['imported']);
        $this->assertDatabaseCount('stocks', 0);
    }
}
