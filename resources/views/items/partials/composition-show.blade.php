<h4 class="mt-4">Состав</h4>

@if ($item->subitems->count())
    <ul class="list-group mb-3">
        @foreach ($item->subitems as $subitem)
            <li class="list-group-item">
                <a href="{{ route('items.show', $subitem) }}">
                    {{ $subitem->name }}
                </a>
                <span class="badge bg-secondary ms-2">
                    × {{ $subitem->pivot->quantity }}
                </span>
            </li>
        @endforeach
    </ul>
@else
    <p class="text-muted">Состав не указан.</p>
@endif
