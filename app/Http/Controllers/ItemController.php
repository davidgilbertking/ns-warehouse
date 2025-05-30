<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
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

class ItemController extends Controller
{
    public function __construct(
        protected ItemService       $service,
        protected ItemExportService $exportService,
        protected ItemImageService  $imageService
    ) {
    }

    public function index(Request $request)
    {
        $filter = ItemFilterDTO::fromArray($request->all());

        try {
            $items = $this->service->getPaginatedItems($filter);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('items.index')->with('error', $e->getMessage());
        }

        return view('items.index', compact('items'));
    }

    public function create()
    {
        $this->authorizeAction();
        $products = Product::orderBy('name')->get();
        return view('items.create', compact('products'));
    }

    public function store(ItemStoreRequest $request)
    {
        $this->authorizeAction();

        $dto = $this->makeStoreDTO($request);
        $this->service->createItem($dto);

        return redirect()->route('items.index')->with('success', 'Предмет создан!');
    }

    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $this->authorizeAction();
        $products = Product::orderBy('name')->get();
        return view('items.edit', compact('item', 'products'));
    }

    public function update(ItemUpdateRequest $request, Item $item)
    {
        $this->authorizeAction();

        $dto = $this->makeUpdateDTO($request);
        $this->service->updateItem($item, $dto);

        return redirect()->route('items.show', $item)->with('success', 'Предмет обновлён!');
    }

    public function destroy(Item $item)
    {
        if ($item->activeReservations()->exists()) {
            return back()->with('error', 'Нельзя удалить предмет, который зарезервирован в мероприятии.');
        }

        if ($item->products()->exists()) {
            return back()->with('error', 'Нельзя удалить предмет, который используется в тэге.');
        }

        $this->service->deleteItem($item);

        return redirect()->route('items.index')->with('success', 'Предмет удалён!');
    }

    public function export(Request $request)
    {
        $filter = ItemFilterDTO::fromArray($request->all());

        $csvContent = $this->exportService->export($filter);
        $filename   = 'items_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    private function authorizeAction(): void
    {
        if (auth()->user()->isViewer()) {
            abort(403, 'Нет прав для этого действия.');
        }
    }

    private function makeStoreDTO(ItemStoreRequest $request): ItemStoreDTO
    {
        $validated = $request->validated();

        return new ItemStoreDTO(
            name:                    $validated['name'],
            description:             $validated['description'] ?? null,
            quantity:                (int)$validated['quantity'],
            size:                    $validated['size'] ?? null,
            material:                $validated['material'] ?? null,
            supplier:                $validated['supplier'] ?? null,
            storageLocation:         $validated['storage_location'] ?? null,
            mechanics:               $validated['mechanics'] ?? null,
            scalability:             $validated['scalability'] ?? null,
            clientPrice:             isset($validated['client_price']) ? (float)$validated['client_price'] : null,
            brandingOptions:         $validated['branding_options'] ?? null,
            adaptationOptions:       $validated['adaptation_options'] ?? null,
            opPrice:                 $validated['op_price'] ?? null,
            constructionDescription: $validated['construction_description'] ?? null,
            contractor:              $validated['contractor'] ?? null,
            productionCost:          $validated['production_cost'] ?? null,
            changeHistory:           $validated['change_history'] ?? null,
            consumables:             $validated['consumables'] ?? null,
            implementationComments:  $validated['implementation_comments'] ?? null,
            mounting:                $validated['mounting'] ?? null,
            storageFeatures:         $validated['storage_features'] ?? null,
            designLinks:             $validated['design_links'] ?? null,
            eventHistory:            $validated['event_history'] ?? null,
            storagePlace:            $validated['storage_place'] ?? null,
            opMedia:                 array_filter($validated['op_media'] ?? []),
            realMedia:               array_filter($validated['real_media'] ?? []),
            eventMedia:              array_filter($validated['event_media'] ?? []),
            images:                  $request->file('images'),
            productIds:              $validated['product_ids'] ?? []

        );
    }

    private function makeUpdateDTO(ItemUpdateRequest $request): ItemUpdateDTO
    {
        $validated = $request->validated();

        return new ItemUpdateDTO(
            name:                    $validated['name'],
            description:             $validated['description'] ?? null,
            quantity:                (int)$validated['quantity'],
            size:                    $validated['size'] ?? null,
            material:                $validated['material'] ?? null,
            supplier:                $validated['supplier'] ?? null,
            storageLocation:         $validated['storage_location'] ?? null,
            mechanics:               $validated['mechanics'] ?? null,
            scalability:             $validated['scalability'] ?? null,
            clientPrice:             isset($validated['client_price']) ? (float)$validated['client_price'] : null,
            brandingOptions:         $validated['branding_options'] ?? null,
            adaptationOptions:       $validated['adaptation_options'] ?? null,
            opPrice:                 $validated['op_price'] ?? null,
            constructionDescription: $validated['construction_description'] ?? null,
            contractor:              $validated['contractor'] ?? null,
            productionCost:          $validated['production_cost'] ?? null,
            changeHistory:           $validated['change_history'] ?? null,
            consumables:             $validated['consumables'] ?? null,
            implementationComments:  $validated['implementation_comments'] ?? null,
            mounting:                $validated['mounting'] ?? null,
            storageFeatures:         $validated['storage_features'] ?? null,
            designLinks:             $validated['design_links'] ?? null,
            eventHistory:            $validated['event_history'] ?? null,
            storagePlace:            $validated['storage_place'] ?? null,
            opMedia:                 array_filter($validated['op_media'] ?? []),
            realMedia:               array_filter($validated['real_media'] ?? []),
            eventMedia:              array_filter($validated['event_media'] ?? []),
            productIds:              $validated['product_ids'] ?? []
        );
    }
}
