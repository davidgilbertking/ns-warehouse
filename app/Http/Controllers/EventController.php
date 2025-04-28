<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Services\EventService;
use App\Services\EventExportService;
use App\Services\ItemQuantityService;
use App\DTOs\EventStoreDTO;
use App\DTOs\EventUpdateDTO;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        protected EventService        $eventService,
        protected EventExportService  $exportService,
        protected ItemQuantityService $itemQuantityService
    ) {
    }

    public function index(Request $request)
    {
        $events = $this->eventService->getFilteredEvents($request->all());

        return view('events.index', compact('events'));
    }

    public function create(Request $request)
    {
        $data = $this->eventService->getCreateData($request->input('product_id'));

        return view('events.create', $data);
    }

    public function store(EventStoreRequest $request)
    {
        $this->authorizeAction();

        $dto = EventStoreDTO::fromArray($request->validated());
        $this->eventService->createEvent($dto);

        return redirect()->route('events.index')->with('success', 'Мероприятие создано!');
    }

    public function show(Event $event)
    {
        $event = $this->eventService->loadEventWithRelations($event);

        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $data = $this->eventService->getEditData($event);

        return view('events.edit', $data);
    }

    public function update(EventUpdateRequest $request, Event $event)
    {
        $this->authorizeAction();

        $dto = EventUpdateDTO::fromArray($request->validated());
        $this->eventService->updateEvent($event, $dto);

        return redirect()->route('events.show', $event)->with('success', 'Мероприятие обновлено!');
    }

    public function destroy(Event $event)
    {
        $this->authorizeAction();

        $this->eventService->deleteEvent($event);

        return redirect()->route('events.index')->with('success', 'Мероприятие удалено!');
    }

    public function exportItems(Event $event)
    {
        $csvContent = $this->exportService->exportItems($event);

        $filename = 'event_items_' . $event->id . '_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    public function cloneEvent(Event $event)
    {
        $this->authorizeAction();

        $data = $this->eventService->getCloneData($event);

        return view('events.create', $data);
    }

    public function checkAvailability(Request $request)
    {
        $availability = $this->itemQuantityService->checkAvailability(
            $request->input('items', []),
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('event_id')
        );

        return response()->json($availability);
    }

    public function getAvailableQuantities(Request $request)
    {
        if (!$request->filled('start_date') || !$request->filled('end_date')) {
            return response()->json([]);
        }

        $availableQuantities = $this->itemQuantityService->getAvailableQuantities(
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json($availableQuantities);
    }

    private function authorizeAction()
    {
        if (auth()->user()?->isViewer()) {
            abort(403, 'Нет прав для этого действия.');
        }
    }
}
