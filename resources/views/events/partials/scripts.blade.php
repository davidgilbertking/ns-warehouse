<script>
    const allItems = @json($items);
    const availableQuantities = @json($availableQuantities);
    const productItems = @json($productItems ?? []);
    const oldItems = @json(old('items', []));
    let selectedItems = {};

    if (Object.keys(oldItems).length > 0) {
        // если есть old('items'), берем их
        Object.values(oldItems).forEach(item => {
            selectedItems[item.id] = {
                id: item.id,
                name: allItems.find(i => i.id == item.id)?.name || 'Неизвестный предмет',
                quantity: item.quantity
            };
        });
    } else if (productItems.length > 0) {
        // иначе, если выбран продукт
        productItems.forEach(item => {
            selectedItems[item.id] = {
                id: item.id,
                name: item.name,
                quantity: item.quantity
            };
        });
    } else if (@json(!empty($preselectedItems))) {
        // иначе, если есть предварительно выбранные предметы
        @foreach ($preselectedItems as $item)
            selectedItems[{{ $item['id'] }}] = {
            id: {{ $item['id'] }},
            name: '{{ $item['name'] }}',
            quantity: {{ $item['quantity'] }}
        };
        @endforeach
    }

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
            <button type="button" class="btn btn-danger btn-sm ms-2" onclick="removeItem(${item.id})">Удалить</button>
        `;
            container.appendChild(div);
        }

        checkAvailability();
    }

    function renderSearchResults() {
        const search = document.getElementById('item-search').value.toLowerCase();
        const container = document.getElementById('search-results');
        container.innerHTML = '';

        if (search.length === 0) {
            return;
        }

        allItems.forEach(item => {
            if (selectedItems[item.id]) return;
            if (!item.name.toLowerCase().includes(search)) return;

            const available = availableQuantities[item.id] ?? 0;

            const div = document.createElement('div');
            div.classList.add('mb-2');
            div.innerHTML = `
                <label>${item.name} (доступно: ${available})</label>
                <input type="number" id="quantity-${item.id}" class="form-control d-inline-block w-auto mx-2" value="1" min="1">
                <button type="button" class="btn btn-success btn-sm" onclick="addItem(${item.id})">Добавить</button>
            `;
            container.appendChild(div);
        });
    }

    function addItem(id) {
        const quantityInput = document.getElementById(`quantity-${id}`);
        const quantity = parseInt(quantityInput.value) || 1;
        const item = allItems.find(i => i.id === id);

        selectedItems[id] = {
            id: item.id,
            name: item.name,
            quantity: quantity
        };

        renderSelectedItems();
        renderSearchResults();
    }

    function removeItem(id) {
        delete selectedItems[id];
        renderSelectedItems();
        renderSearchResults();
    }

    function checkAvailability() {
        const button = document.getElementById('create-event-button') || document.getElementById('save-event-button');
        let hasUnavailable = false;

        const startDate = document.getElementById('start-date')?.value;
        const endDate = document.getElementById('end-date')?.value;

        if (!startDate || !endDate) {
            button.disabled = false;
            return;
        }

        fetch('/events/check-availability', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                start_date: startDate,
                end_date: endDate,
                items: Object.values(selectedItems),
                event_id: document.getElementById('event-id')?.value
            })
        })
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('selected-items');
                container.innerHTML = '';

                for (const id in selectedItems) {
                    const item = selectedItems[id];
                    const available = data[id] ?? 0;
                    const div = document.createElement('div');
                    div.classList.add('mb-2');

                    let extraClass = '';

                    if (available < item.quantity) {
                        extraClass = 'text-danger';
                        hasUnavailable = true;
                    }

                    div.innerHTML = `
    <strong class="${extraClass}">${item.name}</strong>
    <input type="hidden" name="items[${item.id}][id]" value="${item.id}">
    <input type="number" name="items[${item.id}][quantity]" value="${item.quantity}" class="form-control d-inline-block w-auto mx-2" min="1">
    <button type="button" class="btn btn-danger btn-sm ms-2" onclick="removeItem(${item.id})">Удалить</button>
`;
                    container.appendChild(div);
                }

                button.disabled = hasUnavailable;
            });
    }

    document.getElementById('item-search')?.addEventListener('input', renderSearchResults);

    document.getElementById('start-date')?.addEventListener('change', fetchAvailabilityAndUpdate);
    document.getElementById('end-date')?.addEventListener('change', fetchAvailabilityAndUpdate);

    function fetchAvailabilityAndUpdate() {
        const startDate = document.getElementById('start-date')?.value;
        const endDate = document.getElementById('end-date')?.value;

        if (!startDate || !endDate) {
            return;
        }

        fetch(`/api/get-available-quantities?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                for (const id in data) {
                    availableQuantities[id] = data[id];
                }
                renderSelectedItems();
                checkAvailability();
            });
    }

    if (document.getElementById('product-select')) {
        document.getElementById('product-select').addEventListener('change', function () {
            const selectedProductId = this.value;

            if (!selectedProductId) {
                selectedItems = {};
                renderSelectedItems();
                checkAvailability();
                return;
            }

            fetch(`/api/products/${selectedProductId}/items`)
                .then(response => response.json())
                .then(data => {
                    selectedItems = {};
                    data.forEach(item => {
                        selectedItems[item.id] = {
                            id: item.id,
                            name: item.name,
                            quantity: item.quantity
                        };
                    });
                    renderSelectedItems();
                    checkAvailability();
                });
        });
    }

    renderSelectedItems();
    renderSearchResults();

    ['#start-date', '#end-date'].forEach(function (selector) {
        const el = document.querySelector(selector);
        if (el) {
            flatpickr(el, {
                dateFormat: "Y-m-d",
                locale: "ru",
                allowInput: true,
                disableMobile: true,
            });
        }
    });
</script>
