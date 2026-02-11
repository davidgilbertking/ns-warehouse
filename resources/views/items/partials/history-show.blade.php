@if ($item->event_history)
    <p><strong>История проведения:</strong><br>{!! nl2br(e($item->event_history)) !!}</p>
@endif

@if (!empty($item->event_media))
    <div class="mb-3">
        <strong>Медиа с мероприятий:</strong>
        <ul class="mb-0">
            @foreach ($item->event_media as $media)
                <li><a href="{{ $media }}" target="_blank" style="word-break: break-all;">{{ $media }}</a></li>
            @endforeach
        </ul>
    </div>
@endif
