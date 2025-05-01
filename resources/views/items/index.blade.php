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
        <a href="{{ route('items.create') }}" class="btn btn-success  mb-3">Создать предмет</a>
    @endcan
    <h1>Каталог предметов</h1>

    <div class="mb-3">
        <form method="GET" action="{{ route('items.index') }}">
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Поиск по названию..." value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" name="available_from" class="form-control" value="{{ request('available_from') }}">
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" name="available_to" class="form-control" value="{{ request('available_to') }}">
                </div>
                <div class="col-6 col-md-1 d-grid">
                    <a href="{{ route('items.index') }}" class="btn btn-secondary">Очистить</a>
                </div>
                <div class="col-6 col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary">Фильтр</button>
                </div>
            </div>
        </form>
    </div>

    @can('create', App\Models\Item::class)
        <a href="{{ route('items.export', request()->only('search', 'available_from', 'available_to')) }}"
           class="btn btn-info  mb-3">Экспортировать в CSV</a>
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
                    <th>Продукты</th>
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
        {{ $items->links() }}
    @endif

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

            // Новый код для отслеживания отправки формы фильтра
            const form = document.querySelector('form[action="{{ route('items.index') }}"]');
            form.addEventListener('submit', function (e) {
                const search = form.querySelector('input[name="search"]').value;
                const availableFrom = form.querySelector('input[name="available_from"]').value;
                const availableTo = form.querySelector('input[name="available_to"]').value;

                alert(
                    'Отправляемые данные:\n' +
                    'search: ' + search + '\n' +
                    'available_from: ' + availableFrom + '\n' +
                    'available_to: ' + availableTo
                );
            });
        });
    </script>
@endsection
