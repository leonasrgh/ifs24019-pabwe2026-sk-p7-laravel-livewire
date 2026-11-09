<?php

namespace App\Features\Stock\Services;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockExcelService
{
    /** @var array<int, string> */
    protected const HEADERS = ['No', 'Name', 'SKU', 'Description', 'Quantity', 'Unit', 'Price', 'Min Stock', 'Status', 'Created At'];

    /** @var array<int, string> */
    protected const COLUMNS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    public function __construct(
        protected StockRepositoryInterface $stockRepository
    ) {}

    /**
     * Build a Spreadsheet instance containing stocks data.
     *
     * @param Collection<int, Stock> $stocks
     */
    public function buildSpreadsheet(Collection $stocks, string $sheetTitle = 'Stocks'): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($sheetTitle, 0, 31));

        foreach (self::HEADERS as $index => $header) {
            $sheet->setCellValue(self::COLUMNS[$index].'1', $header);
        }

        // Header styling (teks putih di atas biru gelap: kontras tinggi)
        $headerRange = 'A1:J1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E40AF');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNumber = 2;
        foreach ($stocks as $idx => $stock) {
            $sheet->setCellValue("A{$rowNumber}", $idx + 1);
            $sheet->setCellValue("B{$rowNumber}", $stock->name);
            $sheet->setCellValue("C{$rowNumber}", $stock->sku ?? '-');
            $sheet->setCellValue("D{$rowNumber}", $stock->description ?? '-');
            $sheet->setCellValue("E{$rowNumber}", $stock->quantity);
            $sheet->setCellValue("F{$rowNumber}", $stock->unit);
            $sheet->setCellValue("G{$rowNumber}", $stock->price !== null ? (float) $stock->price : '-');
            $sheet->setCellValue("H{$rowNumber}", $stock->min_stock);
            $sheet->setCellValue("I{$rowNumber}", $stock->statusEnum()->label());
            $sheet->setCellValue("J{$rowNumber}", $stock->created_at ? $stock->created_at->format('Y-m-d H:i') : '-');
            $rowNumber++;
        }

        $lastRow = max(2, $rowNumber - 1);
        $sheet->getStyle("A1:J{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (self::COLUMNS as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Export stocks as a downloadable streamed response.
     *
     * @param Collection<int, Stock> $stocks
     */
    public function exportToResponse(Collection $stocks, string $filename = 'stok.xlsx'): StreamedResponse
    {
        $spreadsheet = $this->buildSpreadsheet($stocks);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import stocks from an uploaded spreadsheet file path.
     * Kolom: B=Name, C=SKU, D=Description, E=Quantity, F=Unit, G=Price, H=Min Stock.
     * Status selalu dihitung ulang dari quantity dan min stock.
     *
     * @return array{imported: int, errors: array<int, string>}
     */
    public function import(string $filePath, int $userId): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $imported = 0;
        $errors = [];
        $recordsToInsert = [];
        $now = Carbon::now();

        // Skip header row
        $isFirst = true;

        foreach ($rows as $row) {
            if ($isFirst) {
                $isFirst = false;
                continue;
            }

            $name = isset($row['B']) ? trim((string) $row['B']) : '';

            if ($name === '' || strtolower($name) === 'name') {
                continue;
            }

            $sku = $this->cleanText($row['C'] ?? null);
            $description = $this->cleanText($row['D'] ?? null);
            $quantity = $this->toNonNegativeInt($row['E'] ?? null, 0);
            $unit = $this->cleanText($row['F'] ?? null) ?? 'pcs';
            $price = $this->toPrice($row['G'] ?? null);
            $minStock = $this->toNonNegativeInt($row['H'] ?? null, 5);

            $recordsToInsert[] = [
                'user_id' => $userId,
                'name' => substr($name, 0, 255),
                'sku' => $sku !== null ? substr($sku, 0, 255) : null,
                'description' => $description,
                'quantity' => $quantity,
                'unit' => substr($unit, 0, 30),
                'price' => $price,
                'min_stock' => $minStock,
                'status' => StockStatus::fromQuantity($quantity, $minStock)->value,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $imported++;
        }

        if (!empty($recordsToInsert)) {
            $this->stockRepository->insertMany($recordsToInsert);
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }

    /**
     * Normalise a text cell: trim, and treat empty or "-" as null.
     */
    protected function cleanText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return ($text === '' || $text === '-') ? null : $text;
    }

    /**
     * Convert a cell to a non-negative integer, using the default for blank/invalid values.
     */
    protected function toNonNegativeInt(mixed $value, int $default): int
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '' || !is_numeric($text)) {
            return $default;
        }

        return max(0, (int) round((float) $text));
    }

    /**
     * Convert a cell to a non-negative price or null.
     */
    protected function toPrice(mixed $value): ?float
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '' || !is_numeric($text)) {
            return null;
        }

        return max(0.0, round((float) $text, 2));
    }
}
