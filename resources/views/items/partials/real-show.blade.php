@if (!empty($item->real_media))
    <div class="mb-3">
        <strong>Медиа:</strong>
        <ul class="mb-0">
            @foreach ($item->real_media as $media)
                <li><a href="{{ $media }}" target="_blank">{{ $media }}</a></li>
            @endforeach
        </ul>
    </div>
@endif

@if ($item->construction_description)
    <p><strong>Описание материалов и конструкции:</strong> {{ $item->construction_description }}</p>
@endif

@if ($item->contractor)
    <p><strong>Подрядчик:</strong> {{ $item->contractor }}</p>
@endif

@if ($item->production_cost)
    <p><strong>Стоимость производства/закупки:</strong> {{ $item->production_cost }}</p>
@endif

@if ($item->change_history)
    <p><strong>История изменений:</strong> {{ $item->change_history }}</p>
@endif

@if ($item->consumables)
    <p><strong>Расходники:</strong> {{ $item->consumables }}</p>
@endif

@if ($item->implementation_comments)
    <p><strong>Доп. комментарии:</strong> {{ $item->implementation_comments }}</p>
@endif

@if ($item->mounting)
    <p><strong>Монтаж/демонтаж:</strong> {{ $item->mounting }}</p>
@endif

@if ($item->storage_features)
    <p><strong>Особенности хранения и транспортировки:</strong> {{ $item->storage_features }}</p>
@endif

@if ($item->storage_place)
    <p><strong>Место хранения:</strong> {{ $item->storage_place }}</p>
@endif

@if ($item->design_links)
    <p><strong>Ссылка на макеты/исходники:</strong> <a href="{{ $item->design_links }}" target="_blank">{{ $item->design_links }}</a></p>
@endif
