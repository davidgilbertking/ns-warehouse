@extends('layouts.app')

@section('content')
    <h1>Создать предмет</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Название</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Описание</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label for="size" class="form-label">Размер</label>
                    <input type="text" id="size" name="size" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="material" class="form-label">Материал</label>
                    <input type="text" id="material" name="material" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="supplier" class="form-label">Подрядчик / Магазин</label>
                    <input type="text" id="supplier" name="supplier" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="storage_location" class="form-label">Место хранения</label>
                    <input type="text" id="storage_location" name="storage_location" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="quantity" class="form-label">Количество</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" min="0" value="1" required>
                </div>

                <div class="mb-3">
                    <label for="images" class="form-label">Фотографии</label>
                    <input type="file" id="images" name="images[]" class="form-control" accept="image/*" multiple>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Создать предмет</button>
                <a href="{{ route('items.index') }}" class="btn btn-secondary mt-3 ms-2">Назад</a>
            </form>
        </div>
    </div>
@endsection
