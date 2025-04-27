@extends('layouts.app')

@section('content')
    <h1>Создать предмет</h1>

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
            <form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Название</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Размер</label>
                    <input type="text" name="size" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Материал</label>
                    <input type="text" name="material" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Подрядчик / Магазин</label>
                    <input type="text" name="supplier" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Место хранения</label>
                    <input type="text" name="storage_location" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Количество</label>
                    <input type="number" name="quantity" class="form-control" min="0" value="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Фотографии</label>
                    <input type="file" name="images[]" class="form-control" multiple>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Создать предмет</button>
            </form>
        </div>
    </div>

    <a href="{{ route('items.index') }}" class="btn btn-secondary mt-3">Назад</a>
@endsection
