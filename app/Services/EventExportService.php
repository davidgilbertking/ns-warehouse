<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;

class EventExportService
{
    public function exportItems(Event $event): string
    {
        $event->load('reservations.item');

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['Название мероприятия', $event->name]);
        fputcsv(
            $handle,
            ['Даты проведения', $event->start_date->format('d.m.Y') . ' - ' . $event->end_date->format('d.m.Y')]
        );
        fputcsv($handle, []);

        fputcsv($handle, ['Название предмета', 'Количество']);

        foreach ($event->reservations as $reservation) {
            fputcsv($handle, [
                $reservation->item->name,
                $reservation->quantity,
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }
}
