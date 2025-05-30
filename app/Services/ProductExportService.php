<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;

class ProductExportService
{
    public function export(Product $product)
    {
        $filename = 'product_' . $product->id . '_items.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($product) {
            $handle = fopen('php://output', 'w');

            // Заголовки CSV
            fputcsv($handle, ['Название предмета', 'Количество в тэге']);

            foreach ($product->items as $item) {
                fputcsv($handle, [
                    $item->name,
                    $item->pivot->quantity,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
