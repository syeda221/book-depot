<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Subcategory;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductImportExportController extends Controller
{
    // ──────────────────────────────────────────────────────────
    //  TEMPLATE  –  blank CSV with correct headers
    // ──────────────────────────────────────────────────────────
    public function template()
    {
        $headers = $this->csvHeaders();

        $callback = function () use ($headers) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens it correctly
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            // One sample row so the user understands the format
            fputcsv($handle, [
                '',             // id (leave blank for new)
                '',             // item_code (leave blank for new)
                'Sample Product Name',
                '123456789012', // barcode
                'My Category',  // category name
                'My Sub Category',
                'My Brand',
                '',             // model
                'by_cartons',   // size_mode: by_cartons OR by_pieces
                '12',           // pcs_per_carton
                '500',          // sale_price_per_piece
                '300',          // purchase_price_per_piece
                '0',            // sale_discount_percent
                '0',            // purchase_discount_percent
                '10',           // alert_quantity
                '1',            // is_active (1=yes, 0=no)
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products_template.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    //  EXPORT  –  all products as CSV
    // ──────────────────────────────────────────────────────────
    public function export()
    {
        $products = Product::with([
            'category_relation',
            'sub_category_relation',
            'brand',
        ])->orderBy('id')
          ->get();

        $headers = $this->csvHeaders();

        $callback = function () use ($products, $headers) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            foreach ($products as $p) {
                $sizeMode    = $p->size_mode ?? 'by_cartons';

                fputcsv($handle, [
                    $p->id,
                    $p->item_code,
                    $p->item_name,
                    $p->barcode_path,
                    $p->category_relation->name ?? '',
                    $p->sub_category_relation->name ?? '',
                    $p->brand->name ?? '',
                    $p->model,
                    $sizeMode,
                    $p->pieces_per_box ?? 1,
                    round($p->sale_price_per_piece ?? 0, 2),
                    round($p->purchase_price_per_piece ?? 0, 2),
                    $p->sale_discount_percent ?? 0,
                    $p->purchase_discount_percent ?? 0,
                    $p->alert_quantity ?? 0,
                    $p->is_active ? 1 : 0,
                ]);
            }

            fclose($handle);
        };

        $filename = 'products_export_' . now()->format('Y-m-d_H-i') . '.csv';

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    //  IMPORT  –  parse CSV, upsert products + stock
    // ──────────────────────────────────────────────────────────
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file    = $request->file('csv_file');
        $handle  = fopen($file->getRealPath(), 'r');

        // Remove UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Read header row
        $headerRow = fgetcsv($handle);
        if (! $headerRow) {
            return response()->json(['success' => false, 'message' => 'CSV file is empty or invalid.']);
        }

        // Map header names to index positions (case-insensitive, trim)
        $headerMap = [];
        foreach ($headerRow as $i => $col) {
            $headerMap[strtolower(trim($col))] = $i;
        }

        $requiredCols = ['item_name (*)'];
        foreach ($requiredCols as $col) {
            if (! isset($headerMap[strtolower($col)])) {
                return response()->json([
                    'success' => false,
                    'message' => "Column \"{$col}\" not found. Please use the correct template.",
                ]);
            }
        }

        // Helper to get value from row by column name
        $get = function (array $row, string $colName, $default = '') use ($headerMap) {
            $key = strtolower(trim($colName));
            return isset($headerMap[$key]) && isset($row[$headerMap[$key]])
                ? trim($row[$headerMap[$key]])
                : $default;
        };

        // Preload lookup tables
        $categories    = Category::pluck('id', 'name')->toArray();
        $subCategories = Subcategory::pluck('id', 'name')->toArray();
        $brands        = Brand::pluck('id', 'name')->toArray();

        // Case-insensitive lookup helper
        $lookupId = function (array $map, string $name) {
            if (empty($name)) return null;
            foreach ($map as $k => $v) {
                if (strtolower($k) === strtolower($name)) return $v;
            }
            return null;
        };

        $created  = 0;
        $updated  = 0;
        $skipped  = 0;
        $errors   = [];
        $rowNum   = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            // Skip completely empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $itemName  = $get($row, 'item_name (*)');
            $barcode   = $get($row, 'barcode');
            $itemCode  = $get($row, 'item_code');
            $sizeMode  = $get($row, 'size_mode') ?: 'by_cartons';

            // Validate required field
            if (empty($itemName)) {
                $errors[]  = "Row {$rowNum}: Item Name is required — skipped.";
                $skipped++;
                continue;
            }

            // Validate size_mode
            if (! in_array($sizeMode, ['by_cartons', 'by_pieces', 'by_size'])) {
                $sizeMode = 'by_cartons';
            }

            // Numeric fields with safe defaults
            $pcsPerCarton       = max(1, (int) $get($row, 'pcs_per_carton', 1));
            $salePricePerPiece  = max(0, (float) $get($row, 'sale_price_per_piece', 0));
            $purchPricePerPiece = max(0, (float) $get($row, 'purchase_price_per_piece', 0));
            $saleDisc           = max(0, (float) $get($row, 'sale_discount_%', 0));
            $purchDisc          = max(0, (float) $get($row, 'purchase_discount_%', 0));
            $alertQty           = max(0, (int) $get($row, 'alert_quantity', 0));
            $isActive           = (int) $get($row, 'is_active (1/0)', 1);

            // Lookup category, sub-category, brand IDs
            $categoryId    = $lookupId($categories,    $get($row, 'category'));
            $subCategoryId = $lookupId($subCategories, $get($row, 'sub_category'));
            $brandId       = $lookupId($brands,        $get($row, 'brand'));

            // Calculate derived prices (per carton)
            $salePricePerBox  = $salePricePerPiece  * $pcsPerCarton;
            $purchPricePerBox = $purchPricePerPiece * $pcsPerCarton;

            // ── Try to find existing product ──
            $product = null;

            if (! empty($barcode)) {
                $product = Product::where('barcode_path', $barcode)->first();
            }

            if (! $product && ! empty($itemCode)) {
                $product = Product::where('item_code', $itemCode)->first();
            }

            try {
                DB::transaction(function () use (
                    $product, $itemName, $barcode, $itemCode, $sizeMode,
                    $pcsPerCarton, $salePricePerPiece,
                    $purchPricePerPiece, $salePricePerBox, $purchPricePerBox,
                    $saleDisc, $purchDisc, $alertQty, $isActive,
                    $categoryId, $subCategoryId, $brandId,
                    $get, $row,
                    &$created, &$updated
                ) {
                    $productData = [
                        'item_name'                 => $itemName,
                        'category_id'               => $categoryId,
                        'sub_category_id'           => $subCategoryId,
                        'brand_id'                  => $brandId,
                        'model'                     => $get($row, 'model'),
                        'size_mode'                 => $sizeMode,
                        'pieces_per_box'            => $pcsPerCarton,
                        'sale_price_per_piece'      => $salePricePerPiece,
                        'sale_price_per_box'        => $salePricePerBox,
                        'purchase_price_per_piece'  => $purchPricePerPiece,
                        'purchase_price_per_box'    => $purchPricePerBox,
                        'sale_discount_percent'     => $saleDisc,
                        'purchase_discount_percent' => $purchDisc,
                        'alert_quantity'            => $alertQty,
                        'is_active'                 => $isActive,
                        // Required DB fields with safe defaults for non-by_size products
                        'total_m2'                  => 0,
                        'price_per_m2'              => 0,
                        'purchase_price_per_m2'     => 0,
                        'pieces_per_m2'             => 0,
                        'height'                    => 0,
                        'width'                     => 0,
                    ];

                    if ($product) {
                        // ── UPDATE existing product ──
                        $product->update($productData);

                        $updated++;

                    } else {
                        // ── CREATE new product ──
                        $lastProduct = Product::orderBy('id', 'desc')->first();
                        $nextId      = $lastProduct ? $lastProduct->id + 1 : 1;
                        $newCode     = 'ITEM-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

                        $newProduct = Product::create(array_merge($productData, [
                            'creater_id'    => Auth::id(),
                            'item_code'     => ! empty($itemCode) ? $itemCode : $newCode,
                            'barcode_path'  => ! empty($barcode)  ? $barcode  : rand(100000000000, 999999999999),
                            'is_part'       => 0,
                            'is_assembled'  => 0,
                        ]));

                        // Initial warehouse stock
                        WarehouseStock::create([
                            'warehouse_id' => 1,
                            'product_id'   => $newProduct->id,
                            'quantity'     => 0,
                            'total_pieces' => 0,
                            'remarks'      => 'Initial Stock via Import',
                        ]);

                        $created++;
                    }
                });

            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum} ({$itemName}): " . $e->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        return response()->json([
            'success' => true,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    //  Helper – canonical CSV column headers
    // ──────────────────────────────────────────────────────────
    private function csvHeaders(): array
    {
        return [
            'id',
            'item_code',
            'item_name (*)',
            'barcode',
            'category',
            'sub_category',
            'brand',
            'model',
            'size_mode',
            'pcs_per_carton',
            'sale_price_per_piece',
            'purchase_price_per_piece',
            'sale_discount_%',
            'purchase_discount_%',
            'alert_quantity',
            'is_active (1/0)',
        ];
    }
}
