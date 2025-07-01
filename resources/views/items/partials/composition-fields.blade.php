<h4 class="mt-4">Состав</h4>
<div class="mb-3">
    @foreach ($allSubitems as $subitem)
        <div class="mb-2 border rounded p-2">
            <div class="form-check">
                <input class="form-check-input"
                       type="checkbox"
                       name="subitems[{{ $subitem->id }}][selected]"
                       value="1"
                       id="subitem_{{ $subitem->id }}"
                    {{ (old('subitems.' . $subitem->id . '.selected') || (isset($selectedSubitems) && array_key_exists($subitem->id, $selectedSubitems))) ? 'checked' : '' }}>                <label class="form-check-label" for="subitem_{{ $subitem->id }}">
                    {{ $subitem->name }}
                </label>
            </div>
            <div class="mt-2">
                <label for="subitem_quantity_{{ $subitem->id }}" class="form-label small">Количество:</label>
                <input type="number"
                       class="form-control"
                       min="1"
                       name="subitems[{{ $subitem->id }}][quantity]"
                       id="subitem_quantity_{{ $subitem->id }}"
                       value="{{ old('subitems.' . $subitem->id . '.quantity', $selectedSubitems[$subitem->id] ?? 1) }}">            </div>
        </div>
    @endforeach
</div>
