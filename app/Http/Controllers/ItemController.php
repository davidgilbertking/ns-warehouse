<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with('products');

        if ($request->filled('search')) {
            $search = strtolower($request->search);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(size) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(material) LIKE ?', ['%' . $search . '%']);
            });
        }


        if ($request->filled('available_from') && $request->filled('available_to')) {
            $availableFrom = $request->available_from;
            $availableTo   = $request->available_to;
        }

        $items = $query->paginate(10);

        // Теперь добавим доступное количество к каждому предмету
        if ($request->filled('available_from') && $request->filled('available_to')) {
            foreach ($items as $item) {
                $reserved = $item->reservations()
                                 ->whereHas('event', function ($query) use ($request) {
                                     $query->where(function ($q) use ($request) {
                                         $q->whereBetween('start_date', [$request->available_from, $request->available_to])
                                           ->orWhereBetween('end_date', [$request->available_from, $request->available_to])
                                           ->orWhere(function ($q2) use ($request) {
                                               $q2->where('start_date', '<=', $request->available_from)
                                                  ->where('end_date', '>=', $request->available_to);
                                           });
                                     });
                                 })
                                 ->sum('quantity');

                $item->available_quantity = max(0, $item->quantity - $reserved);
            }
        }

        return view('items.index', compact('items'));
    }

    public function create()
    {
        $this->authorizeAction();
        return view('items.create');
    }

    public function store(Request $request)
    {
        $this->authorizeAction();

        $validated = $request->validate([
                                            'name'              => 'required|string|max:255',
                                            'description'       => 'nullable|string',
                                            'quantity'          => 'required|integer|min:0',
                                            'size'              => 'nullable|string|max:255',
                                            'material'          => 'nullable|string|max:255',
                                            'supplier'          => 'nullable|string|max:255',
                                            'storage_location'  => 'nullable|string|max:255',
                                            'images.*'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                                        ]);

        $item = Item::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('items', 'public');
                $item->images()->create(['path' => $path]);
            }
        }

        if (auth()->check()) {
            ActivityLog::create([
                                    'user_id'     => auth()->id(),
                                    'action'      => 'created_item',
                                    'entity_type' => 'Item',
                                    'entity_id'   => $item->id,
                                    'description' => "Создан предмет: {$item->name}",
                                ]);
        }

        return redirect()->route('items.index')->with('success', 'Предмет создан!');
    }

    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    public function destroy(Item $item)
    {
        if ($item->reservations()->exists()) {
            return back()->with('error', 'Нельзя удалить предмет, который зарезервирован в мероприятии.');
        }

        if ($item->products()->exists()) {
            return back()->with('error', 'Нельзя удалить предмет, который используется в продукте.');
        }

        $item->delete();

        if (auth()->check()) {
            ActivityLog::create([
                                    'user_id' => auth()->id(),
                                    'action' => 'deleted_item',
                                    'entity_type' => 'Item',
                                    'entity_id' => $item->id,
                                    'description' => "Удален предмет: {$item->name}"
                                ]);
        }

        return redirect()->route('items.index')
                         ->with('success', 'Предмет удалён.');
    }

    private function authorizeAction()
    {
        if (auth()->user()->isViewer()) {
            abort(403, 'Нет прав для этого действия.');
        }
    }

    public function edit(Item $item)
    {
        $this->authorizeAction();
        return view('items.edit', compact('item'));
    }

    public function update(Request $request, Item $item)
    {
        $this->authorizeAction();

        $validated = $request->validate([
                                            'name'              => 'required|string|max:255',
                                            'description'       => 'nullable|string',
                                            'quantity'          => 'required|integer|min:0',
                                            'size'              => 'nullable|string|max:255',
                                            'material'          => 'nullable|string|max:255',
                                            'supplier'          => 'nullable|string|max:255',
                                            'storage_location'  => 'nullable|string|max:255',
                                            'images.*'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                                        ]);

        $item->update($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('items', 'public');
                $item->images()->create(['path' => $path]);
            }
        }

        if (auth()->check()) {
            ActivityLog::create([
                                    'user_id'     => auth()->id(),
                                    'action'      => 'updated_item',
                                    'entity_type' => 'Item',
                                    'entity_id'   => $item->id,
                                    'description' => "Изменен предмет: {$item->name}",
                                ]);
        }

        return redirect()->route('items.show', $item)->with('success', 'Предмет обновлён!');
    }

    public function export(Request $request)
    {
        $query = Item::query();

        if ($request->filled('search')) {
            $query->whereRaw('name ILIKE ?', ['%' . $request->search . '%']);
        }

        $items = $query->get();

        if ($request->filled('available_from') && $request->filled('available_to')) {
            foreach ($items as $item) {
                $reserved = $item->reservations()
                                 ->whereHas('event', function ($query) use ($request) {
                                     $query->where(function ($q) use ($request) {
                                         $q->whereBetween('start_date', [$request->available_from, $request->available_to])
                                           ->orWhereBetween('end_date', [$request->available_from, $request->available_to])
                                           ->orWhere(function ($q2) use ($request) {
                                               $q2->where('start_date', '<=', $request->available_from)
                                                  ->where('end_date', '>=', $request->available_to);
                                           });
                                     });
                                 })
                                 ->sum('quantity');

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
                $request->filled('available_from') && $request->filled('available_to') ? ($item->available_quantity ?? '') : '',
            ];
        }

        $filename = 'items_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
        $handle = fopen('php://output', 'w');
        ob_start();

        // ⚡ Вставляем строки с условиями фильтрации:
        fputcsv($handle, ['Фильтрация']);
        fputcsv($handle, ['Дата от', $request->available_from ?: '-']);
        fputcsv($handle, ['Дата до', $request->available_to ?: '-']);
        fputcsv($handle, []); // Пустая строка для разделения

        // Заголовок таблицы
        fputcsv($handle, $csvHeader);

        // Данные
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        $content = ob_get_clean();

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

}
