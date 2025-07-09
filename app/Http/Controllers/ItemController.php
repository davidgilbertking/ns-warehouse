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
        $depth = (int) $request->get('depth', 0);

        $filter = ItemFilterDTO::fromArray($request->all());

        try {
            $items = $this->service->getPaginatedItems($filter, $depth);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('items.index')->with('error', $e->getMessage());
        }

        $entityName = $depth === 1 ? 'Предмет' : 'Задание';
        $entityNamePlural = $depth === 1 ? 'Предметы' : 'Задания';

        return view('items.index', compact('items', 'depth', 'entityName', 'entityNamePlural'));
    }

    public function create(Request $request)
    {
        $this->authorizeAction();

        $depth = (int) $request->get('depth', 0);
        $entityName = $depth === 1 ? 'Предмет' : 'Задание';

        $products = Product::orderBy('name')->get();

        return view('items.create', compact('products', 'depth', 'entityName'));
    }

    public function store(ItemStoreRequest $request)
    {
        $this->authorizeAction();

        $dto = $this->makeStoreDTO($request);
        $this->service->createItem($dto);

        $entityName = $dto->getDepth() === 1 ? 'Предмет' : 'Задание';

        return redirect()->route('items.index', ['depth' => $dto->getDepth()])
                         ->with('success', "Создано: {$entityName}");
    }

    public function show(Item $item)
    {
        $depth = $item->depth;
        $entityName = $depth === 1 ? 'Предмет' : 'Задание';

        $item->load('subitems');
        return view('items.show', compact('item', 'depth', 'entityName'));
    }

    public function edit(Item $item)
    {
        $this->authorizeAction();

        $depth = $item->depth;
        $entityName = $depth === 1 ? 'Предмет' : 'Задание';

        $products = Product::orderBy('name')->get();

        // Получаем выбранные предметы с pivot-данными (quantity)
        $selectedSubitems = $item->subitems()->withPivot('quantity')->get();

        return view('items.edit', compact('item', 'products', 'selectedSubitems', 'depth', 'entityName'));
    }

    public function update(ItemUpdateRequest $request, Item $item)
    {
        $this->authorizeAction();

        $dto = $this->makeUpdateDTO($request);
        $this->service->updateItem($item, $dto);

        $entityName = $item->depth === 1 ? 'Предмет' : 'Задание';

        return redirect()->route('items.show', $item)->with('success', "Обновлено: {$entityName}");
    }

    public function destroy(Item $item)
    {
        $entityName = $item->depth === 1 ? 'Предмет' : 'Задание';

        if ($item->activeReservations()->exists()) {
            return back()->with('error', "Нельзя удалить {$entityName}, которое зарезервировано в мероприятии.");
        }

        if ($item->products()->exists()) {
            return back()->with('error', "Нельзя удалить {$entityName}, к которому привязан тэг.");
        }

        if ($item->depth === 1 && $item->parentItems()->exists()) {
            return back()->with('error', 'Нельзя удалить предмет, который используется в заданиях.');
        }

        if ($item->depth === 0) {
            // Если это задание, удаляем его связи с предметами
            $item->subitems()->detach();
        }

        $this->service->deleteItem($item);

        return redirect()->route('items.index', ['depth' => $item->depth])->with('success', "Удалено: {$entityName}");
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

        // Добавляем depth из query-параметра
        $validated['depth'] = (int)$request->get('depth', 0);

        // Обработка состава: собираем ID => ['selected' => 1, 'quantity' => N]
        $subitemsWithQuantities = [];
        if ($request->filled('subitems')) {
            foreach ($request->input('subitems') as $subitemId => $subitemData) {
                if (isset($subitemData['selected']) && $subitemData['selected']) {
                    $quantity = max(1, (int)($subitemData['quantity'] ?? 1));
                    $subitemsWithQuantities[$subitemId] = [
                        'selected' => 1,
                        'quantity' => $quantity,
                    ];
                }
            }
        }
        $validated['subitems'] = $subitemsWithQuantities;

        // Создаём DTO через fromArray с добавленным depth и подготовленным составом
        return ItemStoreDTO::fromArray($validated);
    }

    private function makeUpdateDTO(ItemUpdateRequest $request): ItemUpdateDTO
    {
        $validated = $request->validated();

        $subitemsWithQuantities = [];
        if ($request->filled('subitems')) {
            foreach ($request->input('subitems') as $subitemId => $subitemData) {
                $subitemsWithQuantities[$subitemId] = [
                    'selected' => isset($subitemData['selected']) && $subitemData['selected'],
                    'quantity' => max(1, (int)($subitemData['quantity'] ?? 1)),
                ];
            }
        }

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
            productIds:              $validated['product_ids'] ?? [],
            subitemsWithQuantities:  $subitemsWithQuantities,
        );
    }

    public function searchSubitems(Request $request)
    {
        $query = $request->get('q');

        $subitems = Item::where('depth', 1)
                        ->when($query, fn($q) => $q->where('name', 'ilike', "%{$query}%"))
                        ->orderBy('name')
                        ->limit(20)
                        ->get(['id', 'name']);

        return response()->json($subitems);
    }
}
