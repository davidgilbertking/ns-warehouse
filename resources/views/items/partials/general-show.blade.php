@if ($item->description)
    <p><strong>Описание:</strong> {{ $item->description }}</p>
@endif

@if ($item->mechanics)
    <p><strong>Механика проведения:</strong> {{ $item->mechanics }}</p>
@endif

@if ($item->size)
    <p><strong>Размер:</strong> {{ $item->size }}</p>
@endif

@if ($item->quantity !== null)
    <p><strong>Количество:</strong> {{ $item->quantity }}</p>
@endif

@if ($item->scalability)
    <p><strong>Проходимость и масштабирование:</strong> {{ $item->scalability }}</p>
@endif

@if ($item->client_price)
    <p><strong>Стоимость для клиента:</strong> {{ $item->client_price }} ₽</p>
@endif

@if ($item->products->isNotEmpty())
    <p><strong>Тэги:</strong>
        {{ $item->products->pluck('name')->implode(', ') }}
    </p>
@endif
