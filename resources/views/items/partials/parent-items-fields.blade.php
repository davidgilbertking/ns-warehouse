<h4 class="mt-4">Задания</h4>

<div class="mb-3">
    <label for="parent-item-selector" class="form-label">Добавить задание:</label>
    <select id="parent-item-selector" class="form-control" style="width: 100%;"></select>
</div>

<div id="parent-items-container" class="mb-3">
    @if (isset($selectedParentItems) && $selectedParentItems->count())
        @foreach ($selectedParentItems as $parentItem)
            <div class="parent-item-entry border rounded p-2 mb-2" data-id="{{ $parentItem->id }}">
                <input type="hidden" name="parent_items[{{ $parentItem->id }}][selected]" value="1">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $parentItem->name }}</strong>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-parent-item">Удалить</button>
                </div>
                <div class="mt-2">
                    <label class="form-label small">Количество в задании:</label>
                    <input type="number" name="parent_items[{{ $parentItem->id }}][quantity]"
                           class="form-control"
                           min="1"
                           value="{{ $parentItem->pivot->quantity ?? 1 }}">
                </div>
            </div>
        @endforeach
    @endif
</div>
