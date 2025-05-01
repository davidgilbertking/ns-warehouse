<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ItemImageService;
use App\Services\ItemService;
use App\Http\Requests\ItemStoreRequest;
use App\Http\Requests\ItemUpdateRequest;
use App\DTOs\ItemFilterDTO;
use App\DTOs\ItemStoreDTO;
use App\DTOs\ItemUpdateDTO;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Services\ItemExportService;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    protected ItemService $service;
    protected ItemExportService $exportService;
    protected ItemImageService $imageService;

    public function __construct(
        ItemService $service,
        ItemExportService $exportService,
        ItemImageService $imageService
    ) {
        $this->service = $service;
        $this->exportService = $exportService;
        $this->imageService = $imageService;
    }


    public function index(Request $request)
    {
        Log::info('Item filter request', $request->all());
        $filter = ItemFilterDTO::fromArray($request->all());
        $items = $this->service->getPaginatedItems($filter);

        return view('items.index', compact('items'));
    }

    public function create()
    {
        $this->authorizeAction();
        return view('items.create');
    }

    public function store(ItemStoreRequest $request)
    {
        $this->authorizeAction();

        $dto = ItemStoreDTO::fromArray($request->validated());
        $item = $this->service->createItem($dto);

        if ($request->hasFile('images')) {
            $this->imageService->uploadImages($item, $request->file('images'));
        }

        return redirect()->route('items.index')->with('success', 'Предмет создан!');
    }

    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $this->authorizeAction();
        return view('items.edit', compact('item'));
    }

    public function update(ItemUpdateRequest $request, Item $item)
    {
        $this->authorizeAction();

        $dto = ItemUpdateDTO::fromArray($request->validated());
        $this->service->updateItem($item, $dto);

        return redirect()->route('items.show', $item)->with('success', 'Предмет обновлён!');
    }

    public function destroy(Item $item)
    {
        if ($item->reservations()->exists()) {
            return back()->with('error', 'Нельзя удалить предмет, который зарезервирован в мероприятии.');
        }

        if ($item->products()->exists()) {
            return back()->with('error', 'Нельзя удалить предмет, который используется в продукте.');
        }

        $this->service->deleteItem($item);

        return redirect()->route('items.index')->with('success', 'Предмет удалён!');
    }

    private function authorizeAction()
    {
        if (auth()->user()->isViewer()) {
            abort(403, 'Нет прав для этого действия.');
        }
    }

    public function export(Request $request)
    {
        $filter = ItemFilterDTO::fromArray($request->all());

        $csvContent = $this->exportService->export($filter);

        $filename = 'items_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
}
