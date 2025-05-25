<div class="mb-3">
    <label for="name" class="form-label">Название</label>
    <input type="text" id="name" name="name" class="form-control" required>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Краткое описание</label>
    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
</div>

<div class="mb-3">
    <label for="mechanics" class="form-label">Механика проведения</label>
    <textarea id="mechanics" name="mechanics" class="form-control" rows="3"></textarea>
</div>

<div class="mb-3">
    <label for="size" class="form-label">Размер</label>
    <input type="text" id="size" name="size" class="form-control">
</div>

<div class="mb-3">
    <label for="quantity" class="form-label">Количество</label>
    <input type="number" id="quantity" name="quantity" class="form-control" min="0" value="1" required>
</div>

<div class="mb-3">
    <label for="scalability" class="form-label">Проходимость и масштабирование</label>
    <textarea id="scalability" name="scalability" class="form-control" rows="2"></textarea>
</div>

<div class="mb-3">
    <label for="client_price" class="form-label">Стоимость для клиента (₽)</label>
    <input type="number" step="0.01" id="client_price" name="client_price" class="form-control">
</div>

<div class="mb-3">
    <label for="product_ids" class="form-label">Тэги</label>
    <select name="product_ids[]" id="product_ids" class="form-select" multiple>
        @foreach ($products as $product)
            <option value="{{ $product->id }}">{{ $product->name }}</option>
        @endforeach
    </select>
    <div class="form-text">Можно выбрать несколько тэгов</div>
</div>

<div class="mb-3">
    <label for="images" class="form-label">Фотографии</label>
    <input type="file" id="images" name="images[]" class="form-control" accept="image/*" multiple>
</div>
