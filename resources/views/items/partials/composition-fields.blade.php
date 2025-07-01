<h4 class="mt-4">Состав</h4>
<div class="mb-3">
    <label for="subitem_ids" class="form-label">Выберите предметы для состава</label>
    <select class="form-select" id="subitem_ids" name="subitem_ids[]" multiple>
        @foreach ($allSubitems as $subitem)
            <option value="{{ $subitem->id }}"
                {{ in_array($subitem->id, old('subitem_ids', $selectedSubitems ?? [])) ? 'selected' : '' }}>
                {{ $subitem->name }}
            </option>
        @endforeach
    </select>
</div>
