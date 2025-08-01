@extends('layouts.app')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @can('create', App\Models\Item::class)
        <a href="{{ route('items.create', ['depth' => $depth]) }}" class="btn btn-success mb-3">
            Создать {{ mb_strtolower($entityName) }}
        </a>
    @endcan
    <h1>Каталог: {{ mb_strtolower($entityNamePlural) }}</h1>

    <div class="mb-3">
        <form method="GET" action="{{ route('items.index') }}">
            <input type="hidden" name="depth" value="{{ $depth }}">
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Поиск по тексту..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-2">
                    <input type="text" id="available_from" name="available_from" class="form-control datepicker"
                           placeholder="От"
                           value="{{ request('available_from') }}" autocomplete="off">
                </div>
                <div class="col-6 col-md-2">
                    <input type="text" id="available_to" name="available_to" class="form-control datepicker"
                           placeholder="До"
                           value="{{ request('available_to') }}" autocomplete="off">
                </div>
                <div class="col-6 col-md-1 d-grid">
                    <a href="{{ route('items.index') }}" class="btn btn-secondary">Очистить</a>
                </div>
                <div class="col-6 col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary">Фильтр</button>
                </div>
                <div class="col-12 col-md-6">
                    <input type="text" name="product" class="form-control" placeholder="Поиск по тэгам..."
                           value="{{ request('product') }}">
                </div>
            </div>
        </form>
    </div>

    @can('create', App\Models\Item::class)
        <a href="{{ route('items.export', request()->only('search', 'available_from', 'available_to', 'product')) }}"
           class="btn btn-info mb-3">Экспортировать список в CSV</a>
    @endcan
    @if ($items->isEmpty())
        <div class="alert alert-warning">
            Нет доступных предметов для выбранных условий.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Тэги</th>
                    <th>Количество всего</th>
                    @if (request()->filled('available_from') && request()->filled('available_to'))
                        <th>Количество доступно</th>
                    @endif
                    @if (auth()->user() && auth()->user()->isAdmin())
                        <th>Действия</th>
                    @endif
                </tr>
                </thead>

                <tbody>
                @foreach ($items as $item)
                    @if (!request()->filled('available_from') || !request()->filled('available_to') || $item->available_quantity > 0)
                        <tr>
                            <td>
                                <a href="{{ route('items.show', $item) }}">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td>{{ $item->description }}</td>
                            <td>
                                @if ($item->products->isNotEmpty())
                                    {{ $item->products->pluck('name')->implode(', ') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $item->quantity }}</td>

                            @if (request()->filled('available_from') && request()->filled('available_to'))
                                <td>{{ $item->available_quantity }}</td>
                            @endif

                            @canany(['update', 'delete'], $item)
                                <td>
                                    @can('update', $item)
                                        <a href="{{ route('items.edit', $item) }}" class="btn btn-warning btn-sm">Редактировать</a>
                                    @endcan
                                    @can('delete', $item)
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-action="{{ route('items.destroy', $item) }}">
                                            Удалить
                                        </button>
                                    @endcan
                                </td>
                            @endcanany
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $items->appends(request()->query())->links() }}
    @endif

    @include('partials.delete-modal')
    @include('partials.date-error-modal')

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

            // Проверка диапазона дат при отправке формы фильтра предметов
            const itemFilterForm = document.querySelector('form[action="{{ route('items.index') }}"]');
            if (itemFilterForm) {
                itemFilterForm.addEventListener('submit', function(e) {
                    const startDate = document.getElementById('available_from').value;
                    const endDate = document.getElementById('available_to').value;

                    if (startDate && endDate && startDate > endDate) {
                        const dateErrorModal = new bootstrap.Modal(document.getElementById('dateErrorModal'));
                        dateErrorModal.show();
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
@endsection
