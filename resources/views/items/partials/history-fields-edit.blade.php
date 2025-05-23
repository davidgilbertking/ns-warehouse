<div class="mb-3">
    <label for="event_history" class="form-label">История проведения</label>
    <textarea id="event_history" name="event_history" class="form-control" rows="3" placeholder="Примеры: 20.04.2024 — Проект X
15.03.2024 — Мероприятие Y
...">{{ old('event_history', $item->event_history) }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Медиа с проектов</label>
    <div id="eventMediaWrapper">
        @php $eventMedia = old('event_media', $item->event_media ?? []); @endphp
        @foreach ($eventMedia as $media)
            <input type="text" name="event_media[]" class="form-control mb-2" value="{{ $media }}" placeholder="Ссылка на папку или фото">
        @endforeach
        <input type="text" name="event_media[]" class="form-control mb-2" placeholder="Ссылка на папку или фото">
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addMediaField('eventMediaWrapper', 'event_media[]')">+ Добавить</button>
</div>
