@if (!empty($item->op_media))
    <div class="mb-3">
        <strong>Медиа:</strong>
        <ul class="mb-0">
            @foreach ($item->op_media as $media)
                <li><a href="{{ $media }}" target="_blank">{{ $media }}</a></li>
            @endforeach
        </ul>
    </div>
@endif

@if ($item->branding_options)
    <p><strong>Варианты брендинга:</strong> {{ $item->branding_options }}</p>
@endif

@if ($item->adaptation_options)
    <p><strong>Варианты адаптации:</strong> {{ $item->adaptation_options }}</p>
@endif

@if ($item->op_price)
    <p><strong>Стоимость для ОП:</strong> {{ $item->op_price }}</p>
@endif
