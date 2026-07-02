<h4 class="mt-4">Задания</h4>

@if ($item->parentItems->count())
    <ul class="list-group mb-3">
        @foreach ($item->parentItems as $parentItem)
            <li class="list-group-item">
                <a href="{{ route('items.show', $parentItem) }}">
                    {{ $parentItem->name }}
                </a>
                <span class="badge bg-secondary ms-2">
                    × {{ $parentItem->pivot->quantity }}
                </span>
            </li>
        @endforeach
    </ul>
@else
    <p class="text-muted">Не входит ни в одно задание.</p>
@endif
