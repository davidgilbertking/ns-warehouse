@extends('layouts.app')

@section('content')
    <h1>Редактировать предмет: {{ $item->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($item->images->count())
        <h5>Текущие фото:</h5>
        <div class="row mb-3">
            @foreach ($item->images as $image)
                <div class="col-md-3 mb-3 position-relative">
                    <img src="{{ asset('storage/' . $image->path) }}" alt="Фото" class="img-fluid rounded">

                    <button type="button"
                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                            style="z-index: 2;"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteModal"
                            data-action="{{ route('items.images.destroy', $image) }}">
                        ×
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    <h5>Добавить новое фото:</h5>
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <form method="POST" action="{{ route('items.images.store', $item) }}" enctype="multipart/form-data" class="mb-4">
                @csrf
                <div class="input-group">
                    <input type="file" name="image" class="form-control" required>
                    <button type="submit" class="btn btn-success">Загрузить фото</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-12 col-md-8 col-lg-6">
            <form method="POST" action="{{ route('items.update', $item) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Название</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $item->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Размер</label>
                    <input type="text" name="size" class="form-control" value="{{ old('size', $item->size ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Материал</label>
                    <input type="text" name="material" class="form-control" value="{{ old('material', $item->material ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Подрядчик / Магазин</label>
                    <input type="text" name="supplier" class="form-control" value="{{ old('supplier', $item->supplier ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Место хранения</label>
                    <input type="text" name="storage_location" class="form-control" value="{{ old('storage_location', $item->storage_location ?? '') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Количество</label>
                    <input type="number" name="quantity" class="form-control" min="0" value="{{ old('quantity', $item->quantity) }}" required>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Сохранить изменения</button>
            </form>
        </div>
    </div>

    <a href="{{ route('items.show', $item) }}" class="btn btn-secondary mt-3">Назад</a>
    @include('partials.delete-modal')
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var action = button.getAttribute('data-action');
                var form = document.getElementById('deleteForm');
                form.action = action;
            });
        });
    </script>
@endsection
