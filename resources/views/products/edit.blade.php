@extends('layouts.app')

@section('content')
    <h1>Редактировать тэг: {{ $product->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <form method="POST" action="{{ route('products.update', $product) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Название тэга</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}"
                           required>
                </div>

                <h4>Выбранные предметы:</h4>
                <div id="selected-items" class="mb-3">
                    <!-- Сюда будут добавляться выбранные предметы -->
                </div>

                <h4>Добавить предмет в тэг:</h4>
                <div class="mb-3">
                    <input type="text" id="item-search" class="form-control" placeholder="Поиск по предметам...">
                </div>
                <div id="search-results" class="mb-3">
                    <!-- Здесь появятся результаты поиска -->
                </div>

                <button type="submit" class="btn btn-primary mt-3"
                        onclick="this.disabled = true; this.innerText = 'Сохраняется…'; this.form.submit();">Сохранить
                    изменения
                </button>
            </form>
        </div>
    </div>

    <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">Назад</a>
@endsection

@section('scripts')
    <script>
        const items = @json(\App\Models\Item::all());
        let selectedItems = {};

        @foreach ($product->items as $item)
            selectedItems[{{ $item->id }}] = {
            id: {{ $item->id }},
            name: '{{ $item->name }}',
            quantity: {{ $item->pivot->quantity }}
        };
        @endforeach

        function renderSelectedItems() {
            const container = document.getElementById('selected-items');
            container.innerHTML = '';

            for (const id in selectedItems) {
                const item = selectedItems[id];
                const div = document.createElement('div');
                div.classList.add('mb-2');
                div.innerHTML = `
                    <strong>${item.name}</strong>
                    <input type="hidden" name="items[${item.id}][id]" value="${item.id}">
                    <input type="number" name="items[${item.id}][quantity]" value="${item.quantity}" class="form-control d-inline-block w-auto mx-2" min="1">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${item.id})">Удалить</button>
                `;
                container.appendChild(div);
            }
        }

        function removeItem(id) {
            delete selectedItems[id];
            renderSelectedItems();
            renderSearchResults();
        }

        function renderSearchResults() {
            const search = document.getElementById('item-search').value.toLowerCase();
            const container = document.getElementById('search-results');
            container.innerHTML = '';

            if (search.length === 0) {
                return;
            }

            items.forEach(item => {
                if (selectedItems[item.id]) return;
                if (!item.name.toLowerCase().includes(search)) return;

                const div = document.createElement('div');
                div.classList.add('mb-2');
                div.innerHTML = `
                    <label>${item.name}</label>
                    <input type="number" id="quantity-${item.id}" class="form-control d-inline-block w-auto mx-2" value="1" min="1">
                    <button type="button" class="btn btn-success btn-sm" onclick="addItem(${item.id})">Добавить</button>
                `;
                container.appendChild(div);
            });
        }

        function addItem(id) {
            const quantityInput = document.getElementById(`quantity-${id}`);
            const quantity = parseInt(quantityInput.value) || 1;
            const item = items.find(i => i.id === id);

            selectedItems[id] = {
                id: item.id,
                name: item.name,
                quantity: quantity
            };

            renderSelectedItems();
            renderSearchResults();
        }

        document.getElementById('item-search').addEventListener('input', renderSearchResults);

        renderSelectedItems();
        renderSearchResults();
    </script>
@endsection
