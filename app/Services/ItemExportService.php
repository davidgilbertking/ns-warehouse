<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ItemFilterDTO;
use App\Repositories\ItemRepository;

class ItemExportService
{
    public function __construct(
        protected ItemRepository $repository
    ) {}

    public function export(ItemFilterDTO $filter): string
    {
        $items = $this->repository->getForExport($filter);

        if ($filter->getAvailableFrom() && $filter->getAvailableTo()) {
            foreach ($items as $item) {
                $reserved = 0;

                foreach ($item->reservations as $reservation) {
                    $event = $reservation->event;
                    if ($event && (
                            ($event->start_date >= $filter->getAvailableFrom() && $event->start_date <= $filter->getAvailableTo()) ||
                            ($event->end_date >= $filter->getAvailableFrom() && $event->end_date <= $filter->getAvailableTo()) ||
                            ($event->start_date <= $filter->getAvailableFrom() && $event->end_date >= $filter->getAvailableTo())
                        )) {
                        $reserved += $reservation->quantity;
                    }
                }

                $item->available_quantity = max(0, $item->quantity - $reserved);
            }

            $items = $items->filter(function ($item) {
                return $item->available_quantity > 0;
            });
        }

        $csvHeader = ['Название', 'Описание', 'Количество всего', 'Количество доступно'];
        $csvData = [];

        foreach ($items as $item) {
            $csvData[] = [
                $item->name,
                $item->description,
                $item->quantity,
                $filter->getAvailableFrom() && $filter->getAvailableTo() ? ($item->available_quantity ?? '') : '',
            ];
        }

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['Фильтрация']);
        fputcsv($handle, ['Дата от', $filter->getAvailableFrom() ?? '-']);
        fputcsv($handle, ['Дата до', $filter->getAvailableTo() ?? '-']);
        fputcsv($handle, []);

        fputcsv($handle, $csvHeader);

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }
}
