<h4 class="mt-4">Состав</h4>

@if ($item->subitems->count())
    <ul class="list-group mb-3">
        @foreach ($item->subitems as $subitem)
            <li class="list-group-item">
                <a href="{{ route('items.show', $subitem) }}">
                    {{ $subitem->name }}
                </a>
            </li>
        @endforeach
    </ul>
@else
    <p class="text-muted">Состав не указан.</p>
@endif
