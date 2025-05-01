@extends('layouts.app')

@section('content')
    <h1>Редактировать мероприятие: {{ $event->name }}</h1>

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
            <form method="POST" action="{{ route('events.update', $event) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Название</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $event->name) }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Дата начала</label>
                    <input type="text" id="start-date" name="start_date" class="form-control datepicker" placeholder="От"
                           value="{{ old('start_date', $event->start_date->format('Y-m-d')) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Дата окончания</label>
                    <input type="text" id="end-date" name="end_date" class="form-control datepicker" placeholder="До"
                           value="{{ old('end_date', $event->end_date->format('Y-m-d')) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Продукт</label>
                    <select id="product-select" class="form-select">
                        <option value="">-- Выберите продукт --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <h4>Выбранные предметы:</h4>
                <div id="selected-items" class="mb-3">
                    <!-- Тут будут выбранные предметы -->
                </div>

                <h4>Добавить предмет:</h4>
                <div class="mb-3">
                    <input type="text" id="item-search" class="form-control" placeholder="Поиск по предметам...">
                </div>

                <div id="search-results" class="mb-3"></div>

                <input type="hidden" id="event-id" value="{{ $event->id }}">

                <button type="submit" id="save-event-button" class="btn btn-primary mt-3">Сохранить изменения</button>
            </form>
        </div>
    </div>

    <a href="{{ route('events.show', $event) }}" class="btn btn-secondary mt-3">Назад</a>
@endsection

@section('scripts')
    @include('events.partials.scripts')
@endsection
