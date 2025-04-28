<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\Item;
use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', '%' . $request->search . '%');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        if (!$request->boolean('show_archive')) {
            $query->where('end_date', '>=', now()->startOfDay());
        }

        $events = $query->orderBy('start_date')->paginate(10)->withQueryString();

        return view('events.index', compact('events'));
    }

    public function create(Request $request)
    {
        $items               = Item::select('id', 'name', 'quantity')->get();
        $products            = Product::select('id', 'name')->get();
        $availableQuantities = [];

        foreach ($items as $item) {
            $reserved = $item->reservations()
                             ->whereHas('event', function ($query) {
                                 $query->where('end_date', '>=', now());
                             })
                             ->sum('quantity');

            $availableQuantities[$item->id] = max(0, $item->quantity - $reserved);
        }

        $preselectedItems = [];

        if ($request->filled('product_id')) {
            $product = Product::find($request->product_id);

            if ($product) {
                foreach ($product->items as $item) {
                    $preselectedItems[] = [
                        'id'       => $item->id,
                        'name'     => $item->name,
                        'quantity' => 1,
                    ];
                }
            }
        }

        return view('events.create', compact('items', 'products', 'availableQuantities', 'preselectedItems'));
    }

    public function store(Request $request)
    {
        $this->authorizeAction();

        $validated = $request->validate([
                                            'name'             => 'required|string|max:255',
                                            'start_date'       => 'required|date',
                                            'end_date'         => 'required|date|after_or_equal:start_date',
                                            'items'            => 'nullable|array',
                                            'items.*.id'       => 'exists:items,id',
                                            'items.*.quantity' => 'integer|min:1',
                                        ]);

        // Проверка доступности
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $itemModel = Item::find($item['id']);
                $available =
                    $this->getAvailableQuantityForItem($item['id'], $validated['start_date'], $validated['end_date']);

                if ($item['quantity'] > $available) {
                    return back()
                        ->withErrors([
                                         'items' => "Недостаточно доступных единиц для предмета \"{$itemModel->name}\" (доступно: {$available})",
                                     ])
                        ->withInput();
                }
            }
        }

        $event = Event::create([
                                   'name'       => $validated['name'],
                                   'start_date' => $validated['start_date'],
                                   'end_date'   => $validated['end_date'],
                                   'user_id'    => auth()->id(),
                               ]);

        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                Reservation::create([
                                        'event_id' => $event->id,
                                        'item_id'  => $item['id'],
                                        'quantity' => $item['quantity'],
                                    ]);
            }
        }

        ActivityLog::create([
                                'user_id'     => auth()->id(),
                                'action'      => 'created_event',
                                'entity_type' => 'Event',
                                'entity_id'   => $event->id,
                                'description' => "Создано мероприятие: {$event->name}",
                            ]);

        return redirect()->route('events.index')->with('success', 'Мероприятие создано!');
    }

    public function show(Event $event)
    {
        $event->load('reservations.item', 'user');
        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $items               = Item::all();
        $availableQuantities = [];
        $products            = Product::all();

        foreach ($items as $item) {
            $reserved = $item->reservations()
                             ->whereHas('event', function ($query) use ($event) {
                                 $query->where('end_date', '>=', now())
                                       ->where('id', '!=', $event->id);
                             })
                             ->sum('quantity');

            $availableQuantities[$item->id] = max(0, $item->quantity - $reserved);
        }

        // !!! ВАЖНО: собрать выбранные предметы мероприятия для скрипта
        $preselectedItems = [];
        foreach ($event->reservations as $reservation) {
            $preselectedItems[] = [
                'id'       => $reservation->item_id,
                'name'     => $reservation->item->name,
                'quantity' => $reservation->quantity,
            ];
        }

        return view('events.edit', compact('event', 'items', 'availableQuantities', 'products', 'preselectedItems'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeAction();

        $validated = $request->validate([
                                            'name'             => 'required|string|max:255',
                                            'start_date'       => 'required|date',
                                            'end_date'         => 'required|date|after_or_equal:start_date',
                                            'items'            => 'nullable|array',
                                            'items.*.id'       => 'required|exists:items,id',
                                            'items.*.quantity' => 'required|integer|min:1',
                                        ]);

        // Проверка доступности (учитываем, что текущее мероприятие ещё существует)
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $itemModel = Item::find($item['id']);
                $available = $this->getAvailableQuantityForItem(
                    $item['id'], $validated['start_date'], $validated['end_date'], $event->id
                );

                if ($item['quantity'] > $available) {
                    return back()
                        ->withErrors([
                                         'items' => "Недостаточно доступных единиц для предмета \"{$itemModel->name}\" (доступно: {$available})",
                                     ])
                        ->withInput();
                }
            }
        }

        $event->update([
                           'name'       => $validated['name'],
                           'start_date' => $validated['start_date'],
                           'end_date'   => $validated['end_date'],
                       ]);

        $event->reservations()->delete();

        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $event->reservations()->create([
                                                   'item_id'  => $item['id'],
                                                   'quantity' => $item['quantity'],
                                               ]);
            }
        }

        ActivityLog::create([
                                'user_id'     => auth()->id(),
                                'action'      => 'updated_event',
                                'entity_type' => 'Event',
                                'entity_id'   => $event->id,
                                'description' => "Изменено мероприятие: {$event->name}",
                            ]);

        return redirect()->route('events.show', $event)->with('success', 'Мероприятие обновлено!');
    }

    public function destroy(Event $event)
    {
        $this->authorizeAction();

        $event->delete();

        ActivityLog::create([
                                'user_id'     => auth()->id(),
                                'action'      => 'deleted_event',
                                'entity_type' => 'Event',
                                'entity_id'   => $event->id,
                                'description' => "Удалено мероприятие: {$event->name}",
                            ]);

        return redirect()->route('events.index')->with('success', 'Мероприятие удалено!');
    }

    public function exportItems(Event $event)
    {
        $event->load('reservations.item');

        $csvHeader = ['Название мероприятия', $event->name];
        $csvDates  =
            ['Даты проведения', $event->start_date->format('d.m.Y') . ' - ' . $event->end_date->format('d.m.Y')];

        $csvColumns = ['Название предмета', 'Количество'];
        $csvData    = [];

        foreach ($event->reservations as $reservation) {
            $csvData[] = [
                $reservation->item->name,
                $reservation->quantity,
            ];
        }

        $filename = 'event_items_' . $event->id . '_' . now()->format('Y_m_d_H_i_s') . '.csv';
        $handle   = fopen('php://output', 'w');
        ob_start();

        fputcsv($handle, $csvHeader);
        fputcsv($handle, $csvDates);
        fputcsv($handle, []);
        fputcsv($handle, $csvColumns);

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        $content = ob_get_clean();

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    public function cloneEvent(Event $event)
    {
        $this->authorizeAction();

        $items               = Item::select('id', 'name', 'quantity')->get();
        $products            = Product::select('id', 'name')->get();
        $availableQuantities = [];

        foreach ($items as $item) {
            $reserved = $item->reservations()
                             ->whereHas('event', function ($query) {
                                 $query->where('end_date', '>=', now());
                             })
                             ->sum('quantity');

            $availableQuantities[$item->id] = max(0, $item->quantity - $reserved);
        }

        $preselectedItems = [];

        foreach ($event->reservations as $reservation) {
            $preselectedItems[] = [
                'id'       => $reservation->item_id,
                'name'     => $reservation->item->name,
                'quantity' => $reservation->quantity,
            ];
        }

        $defaultName = $event->name . ' (копия)';

        return view(
            'events.create', compact('items', 'products', 'availableQuantities', 'preselectedItems', 'defaultName')
        );
    }

    public function checkAvailability(Request $request)
    {
        $start   = $request->input('start_date');
        $end     = $request->input('end_date');
        $items   = $request->input('items', []);
        $eventId = $request->input('event_id'); // <-- добавляем

        $availability = [];

        foreach ($items as $item) {
            $reserved = Reservation::where('item_id', $item['id'])
                                   ->whereHas('event', function ($query) use ($start, $end, $eventId) {
                                       $query->where(function ($q) use ($start, $end) {
                                           $q->whereBetween('start_date', [$start, $end])
                                             ->orWhereBetween('end_date', [$start, $end])
                                             ->orWhere(function ($q2) use ($start, $end) {
                                                 $q2->where('start_date', '<=', $start)
                                                    ->where('end_date', '>=', $end);
                                             });
                                       });
                                       if ($eventId) {
                                           $query->where(
                                               'id', '!=', $eventId
                                           ); // <-- исключаем редактируемое мероприятие
                                       }
                                   })
                                   ->sum('quantity');

            $itemModel = Item::find($item['id']);

            if ($itemModel) {
                $available                 = max(0, $itemModel->quantity - $reserved);
                $availability[$item['id']] = $available;
            }
        }

        return response()->json($availability);
    }

    public function getAvailableQuantities(Request $request)
    {
        $availableQuantities = [];

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $items = Item::select('id', 'quantity')->get();

            foreach ($items as $item) {
                $reserved = $item->reservations()
                                 ->whereHas('event', function ($query) use ($request) {
                                     $query->where(function ($q) use ($request) {
                                         $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                                           ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                                           ->orWhere(function ($q2) use ($request) {
                                               $q2->where('start_date', '<=', $request->start_date)
                                                  ->where('end_date', '>=', $request->end_date);
                                           });
                                     });
                                 })
                                 ->sum('quantity');

                $availableQuantities[$item->id] = max(0, $item->quantity - $reserved);
            }
        }

        return response()->json($availableQuantities);
    }

    private function authorizeAction()
    {
        if (auth()->user()?->isViewer()) {
            abort(403, 'Нет прав для этого действия.');
        }
    }

    private function getAvailableQuantityForItem($itemId, $startDate, $endDate, $excludeEventId = null)
    {
        $query = \App\Models\Reservation::where('item_id', $itemId)
                                        ->whereHas('event', function ($q) use ($startDate, $endDate, $excludeEventId) {
                                            $q->where(function ($query) use ($startDate, $endDate) {
                                                $query->whereBetween('start_date', [$startDate, $endDate])
                                                      ->orWhereBetween('end_date', [$startDate, $endDate])
                                                      ->orWhere(function ($q2) use ($startDate, $endDate) {
                                                          $q2->where('start_date', '<=', $startDate)
                                                             ->where('end_date', '>=', $endDate);
                                                      });
                                            });

                                            if ($excludeEventId) {
                                                $q->where('id', '!=', $excludeEventId);
                                            }
                                        });

        $reserved = $query->sum('quantity');

        $item = \App\Models\Item::find($itemId);

        if (!$item) {
            return 0;
        }

        return max(0, $item->quantity - $reserved);
    }
}
