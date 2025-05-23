<div class="mb-3">
    <label class="form-label">Медиа (фото/видео)</label>
    <div id="opMediaWrapper">
        @php $opMedia = old('op_media', $item->op_media ?? []); @endphp
        @foreach ($opMedia as $media)
            <input type="text" name="op_media[]" class="form-control mb-2" value="{{ $media }}" placeholder="Ссылка на медиа">
        @endforeach
        <input type="text" name="op_media[]" class="form-control mb-2" placeholder="Ссылка на медиа">
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addMediaField('opMediaWrapper', 'op_media[]')">+ Добавить</button>
</div>

<div class="mb-3">
    <label for="branding_options" class="form-label">Варианты брендинга</label>
    <textarea id="branding_options" name="branding_options" class="form-control" rows="2">{{ old('branding_options', $item->branding_options) }}</textarea>
</div>

<div class="mb-3">
    <label for="adaptation_options" class="form-label">Варианты адаптации</label>
    <textarea id="adaptation_options" name="adaptation_options" class="form-control" rows="2">{{ old('adaptation_options', $item->adaptation_options) }}</textarea>
</div>

<div class="mb-3">
    <label for="op_price" class="form-label">Стоимость для ОП</label>
    <input type="text" id="op_price" name="op_price" class="form-control" value="{{ old('op_price', $item->op_price) }}">
</div>
