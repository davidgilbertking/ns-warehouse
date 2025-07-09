<h4 class="mt-4">Состав</h4>

<div class="mb-3">
    <label for="subitem-selector" class="form-label">Добавить предмет в состав:</label>
    <select id="subitem-selector" class="form-control" style="width: 100%;"></select>
</div>

<div id="subitems-container" class="mb-3">
    {{-- Уже выбранные предметы будут отображаться здесь --}}
    @if (isset($selectedSubitems) && $selectedSubitems->count())
        @foreach ($selectedSubitems as $subitem)
            <div class="subitem-entry border rounded p-2 mb-2" data-id="{{ $subitem->id }}">
                <input type="hidden" name="subitems[{{ $subitem->id }}][selected]" value="1">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $subitem->name }}</strong>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-subitem">Удалить</button>
                </div>
                <div class="mt-2">
                    <label class="form-label small">Количество:</label>
                    <input type="number" name="subitems[{{ $subitem->id }}][quantity]"
                           class="form-control"
                           min="1"
                           value="{{ $subitem->pivot->quantity }}">
                </div>
            </div>
        @endforeach
    @endif
</div>
