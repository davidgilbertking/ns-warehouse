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
            <div class="item-filter-layout">
                <div class="item-filter-fields">
                    <input type="text" name="search" class="form-control" placeholder="Поиск по тексту..."
                           value="{{ request('search') }}">

                    {{--
                    <input type="text" id="available_from" name="available_from" class="form-control datepicker"
                           placeholder="От"
                           value="{{ request('available_from') }}" autocomplete="off">
                    <input type="text" id="available_to" name="available_to" class="form-control datepicker"
                           placeholder="До"
                           value="{{ request('available_to') }}" autocomplete="off">
                    --}}

                    <input type="text" name="product" class="form-control" placeholder="Поиск по тэгам..."
                           value="{{ request('product') }}">

                    <input type="text" name="storage_place" class="form-control" placeholder="Поиск по месту..."
                           value="{{ request('storage_place') }}">

                    @if ($depth === 1)
                        <div class="item-filter-checkbox">
                            <div class="form-check mb-0">
                                <input type="checkbox"
                                       name="without_parent_items"
                                       id="without_parent_items"
                                       value="1"
                                       class="form-check-input"
                                       @checked(request()->boolean('without_parent_items'))>
                                <label for="without_parent_items" class="form-check-label">
                                    Без заданий
                                </label>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="item-filter-actions">
                    <a href="{{ route('items.index', ['depth' => $depth]) }}" class="btn btn-secondary">Очистить</a>
                    <button type="submit" class="btn btn-primary">Искать</button>
                </div>
            </div>
        </form>
    </div>

    @can('create', App\Models\Item::class)
        <a href="{{ route('items.export', request()->only('depth', 'search', 'available_from', 'available_to', 'product', 'storage_place', 'without_parent_items')) }}"
           class="btn btn-info mb-3">Экспортировать список в CSV</a>
    @endcan
    @if ($items->isEmpty())
        <div class="alert alert-warning">
            Нет доступных предметов для выбранных условий.
        </div>
    @else
        <div class="items-catalog-mobile d-md-none">
            @foreach ($items as $item)
                @if (!request()->filled('available_from') || !request()->filled('available_to') || $item->available_quantity > 0)
                    <article class="items-catalog-card">
                        <div class="items-catalog-card-header">
                            <a href="{{ route('items.show', $item) }}" class="items-catalog-card-title">
                                {{ $item->name }}
                            </a>
                            <span class="badge text-bg-secondary items-catalog-card-quantity">
                                {{ $item->quantity }} шт.
                            </span>
                        </div>

                        <div class="items-catalog-card-row">
                            <span class="items-catalog-card-label">Описание</span>
                            <span class="items-catalog-card-value">{{ $item->description ?: '—' }}</span>
                        </div>

                        <div class="items-catalog-card-row">
                            <span class="items-catalog-card-label">Место</span>
                            <span class="items-catalog-card-value">{{ $item->storage_place ?: '—' }}</span>
                        </div>

                        <div class="items-catalog-card-row">
                            <span class="items-catalog-card-label">Тэги</span>
                            <span class="items-catalog-card-value">
                                @if ($item->products->isNotEmpty())
                                    {{ $item->products->pluck('name')->implode(', ') }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>

                        @if ($depth === 1)
                            <div class="items-catalog-card-row">
                                <span class="items-catalog-card-label">Задания</span>
                                <span class="items-catalog-card-value">
                                    @if ($item->parentItems->isNotEmpty())
                                        @foreach ($item->parentItems as $parentItem)
                                            <a href="{{ route('items.show', $parentItem) }}" class="catalog-parent-item-link">
                                                {{ $parentItem->name }}
                                            </a>
                                        @endforeach
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                        @endif

                        @if (request()->filled('available_from') && request()->filled('available_to'))
                            <div class="items-catalog-card-row">
                                <span class="items-catalog-card-label">Доступно</span>
                                <span class="items-catalog-card-value">{{ $item->available_quantity }}</span>
                            </div>
                        @endif

                        @canany(['update', 'delete'], $item)
                            <div class="items-catalog-card-actions">
                                @can('update', $item)
                                    <a href="{{ route('items.edit', $item) }}" class="btn btn-warning btn-sm">Редактировать</a>
                                @endcan
                                @can('delete', $item)
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            data-action="{{ route('items.destroy', ['item' => $item] + request()->query()) }}">
                                        Удалить
                                    </button>
                                @endcan
                            </div>
                        @endcanany
                    </article>
                @endif
            @endforeach
        </div>

        <div class="table-responsive d-none d-md-block">
            <table class="table table-bordered items-catalog-table {{ $depth === 1 ? 'items-catalog-table-items' : 'items-catalog-table-tasks' }}">
                <colgroup>
                    <col class="catalog-col-name">
                    <col class="catalog-col-description">
                    <col class="catalog-col-place">
                    <col class="catalog-col-products">
                    @if ($depth === 1)
                        <col class="catalog-col-parent-items">
                    @endif
                    <col class="catalog-col-quantity">
                    @if (request()->filled('available_from') && request()->filled('available_to'))
                        <col class="catalog-col-available-quantity">
                    @endif
                    @if (auth()->user() && auth()->user()->isAdmin())
                        <col class="catalog-col-actions">
                    @endif
                </colgroup>
                <thead>
                <tr>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Место</th>
                    <th>Тэги</th>
                    @if ($depth === 1)
                        <th>Задания</th>
                    @endif
                    <th>Кол-во</th>
                    @if (request()->filled('available_from') && request()->filled('available_to'))
                        <th>Доступно</th>
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
                            <td>{{ $item->storage_place }}</td>
                            <td>
                                @if ($item->products->isNotEmpty())
                                    {{ $item->products->pluck('name')->implode(', ') }}
                                @else
                                    —
                                @endif
                            </td>
                            @if ($depth === 1)
                                <td>
                                    @if ($item->parentItems->isNotEmpty())
                                        @foreach ($item->parentItems as $parentItem)
                                            <a href="{{ route('items.show', $parentItem) }}" class="catalog-parent-item-link">
                                                {{ $parentItem->name }}
                                            </a>
                                        @endforeach
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
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
                                                data-action="{{ route('items.destroy', ['item' => $item] + request()->query()) }}">
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
    {{-- @include('partials.date-error-modal') --}}

@endsection
@section('styles')
    <style>
        .item-filter-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.5rem;
            align-items: start;
        }

        .item-filter-fields {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
            gap: 0.5rem;
        }

        .item-filter-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .item-filter-actions .btn {
            min-width: 5.5rem;
        }

        .item-filter-checkbox {
            display: flex;
            align-items: center;
            min-height: calc(1.5em + 0.75rem + 2px);
        }

        .items-catalog-mobile {
            display: grid;
            gap: 0.75rem;
        }

        .items-catalog-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.375rem;
            background: var(--bs-body-bg);
            padding: 0.75rem;
        }

        .items-catalog-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .items-catalog-card-title {
            min-width: 0;
            font-weight: 600;
            overflow-wrap: break-word;
        }

        .items-catalog-card-quantity {
            flex: 0 0 auto;
            white-space: nowrap;
        }

        .items-catalog-card-row {
            display: grid;
            grid-template-columns: 6rem minmax(0, 1fr);
            gap: 0.5rem;
            padding: 0.45rem 0;
            border-top: 1px solid var(--bs-border-color);
        }

        .items-catalog-card-label {
            color: var(--bs-secondary-color);
            font-size: 0.875rem;
        }

        .items-catalog-card-value {
            min-width: 0;
            overflow-wrap: break-word;
        }

        .items-catalog-card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--bs-border-color);
        }

        .items-catalog-card-actions .btn {
            flex: 1 1 8rem;
            white-space: nowrap;
        }

        .items-catalog-card .catalog-parent-item-link {
            display: block;
            margin-bottom: 0.25rem;
        }

        @media (max-width: 991.98px) {
            .item-filter-layout {
                grid-template-columns: 1fr;
            }

            .item-filter-actions {
                justify-content: stretch;
            }

            .item-filter-actions .btn {
                flex: 1;
            }
        }

        @media (max-width: 374.98px) {
            .items-catalog-card-row {
                grid-template-columns: 1fr;
                gap: 0.125rem;
            }
        }

        .items-catalog-table {
            table-layout: fixed;
        }

        .items-catalog-table th,
        .items-catalog-table td {
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        .items-catalog-table .catalog-parent-item-link {
            display: block;
            margin-bottom: 0.25rem;
        }

        .items-catalog-table .catalog-col-quantity,
        .items-catalog-table .catalog-col-available-quantity {
            width: 6rem;
        }

        .items-catalog-table .catalog-col-actions {
            width: 10rem;
        }

        .items-catalog-table-items .catalog-col-name {
            width: 16%;
        }

        .items-catalog-table-items .catalog-col-description {
            width: 31%;
        }

        .items-catalog-table-items .catalog-col-place {
            width: 8%;
        }

        .items-catalog-table-items .catalog-col-products {
            width: 10%;
        }

        .items-catalog-table-items .catalog-col-parent-items {
            width: 16%;
        }

        .items-catalog-table-tasks .catalog-col-name {
            width: 17%;
        }

        .items-catalog-table-tasks .catalog-col-description {
            width: 43%;
        }

        .items-catalog-table-tasks .catalog-col-place {
            width: 9%;
        }

        .items-catalog-table-tasks .catalog-col-products {
            width: 13%;
        }
    </style>
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

            {{--
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
            --}}
        });
    </script>
@endsection
