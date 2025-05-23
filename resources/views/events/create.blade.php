@extends('layouts.app')

@section('content')
    <h1>Создать мероприятие</h1>

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
            <form method="POST" action="{{ route('events.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Название</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $defaultName ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Дата начала</label>
                    <input type="text" id="start-date" name="start_date" class="form-control datepicker" placeholder="От" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Дата окончания</label>
                    <input type="text" id="end-date" name="end_date" class="form-control datepicker" placeholder="До" required>
                </div>

                @if (!request()->has('product_id'))
                    <div class="mb-3">
                        <label class="form-label">Тэг</label>
                        <select id="product-select" class="form-select">
                            <option value="">-- Выберите тэг --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <h4>Выбранные предметы:</h4>
                <div id="selected-items" class="mb-3">
                    <!-- Сюда будут добавляться выбранные предметы -->
                </div>

                <h4>Добавить предмет:</h4>
                <div class="mb-3">
                    <input type="text" id="item-search" class="form-control" placeholder="Поиск по предметам...">
                </div>
                <div id="search-results" class="mb-3">
                    <!-- Здесь будут появляться найденные предметы -->
                </div>

                <button type="submit" id="create-event-button" class="btn btn-primary mt-3">Создать мероприятие</button>
            </form>
        </div>
    </div>

    <a href="{{ route('events.index') }}" class="btn btn-secondary mt-3">Назад</a>
@endsection

@section('scripts')
    @include('events.partials.scripts')
@endsection
