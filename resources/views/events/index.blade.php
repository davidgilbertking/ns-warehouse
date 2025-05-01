@extends('layouts.app')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @can('create', App\Models\Event::class)
        <a href="{{ route('events.create') }}" class="btn btn-success mb-3">Создать мероприятие</a>
    @endcan
    <h1>Мероприятия</h1>

    @if ($events->isEmpty())
        <div class="alert alert-warning mb-3">
            Нет мероприятий для отображения.
        </div>

        <div class="mb-3">
            <a href="{{ route('events.index') }}" class="btn btn-secondary">Очистить фильтры</a>
        </div>
    @else

        <form method="GET" action="{{ route('events.index') }}" class="mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="show_archive" name="show_archive" value="1"
                       {{ request('show_archive') ? 'checked' : '' }} onchange="this.form.submit()">
                <label class="form-check-label" for="show_archive">Показать архив мероприятий</label>
            </div>
        </form>

        <div class="mb-3">
            <form id="filter-form" method="GET" action="{{ route('events.index') }}">
                <div class="row g-2">
                    <div class="col-12 col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Поиск по названию..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <input type="text" id="start_date" name="start_date" class="form-control datepicker"
                               placeholder="От"
                               value="{{ request('start_date') }}" autocomplete="off">
                    </div>
                    <div class="col-6 col-md-2">
                        <input type="text" id="end_date" name="end_date" class="form-control datepicker"
                               placeholder="До"
                               value="{{ request('end_date') }}" autocomplete="off">
                    </div>
                    <div class="col-6 col-md-1 d-grid">
                        <a href="{{ route('events.index') }}" class="btn btn-secondary">Очистить</a>
                    </div>
                    <div class="col-6 col-md-1 d-grid">
                        <input type="hidden" name="show_archive" value="{{ request('show_archive') ? 1 : 0 }}">
                        <button type="submit" class="btn btn-primary">Фильтр</button>
                    </div>
                </div>
            </form>
        </div>


        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Название</th>
                    <th>Даты</th>
                    @if (auth()->user() && auth()->user()->isAdmin())
                        <th>Действия</th>
                    @endif
                </tr>
                </thead>

                <tbody>
                @foreach ($events as $event)
                    <tr>
                        <td>
                            <a href="{{ route('events.show', $event) }}">
                                {{ $event->name }}
                            </a>
                        </td>
                        <td>{{ \Illuminate\Support\Carbon::parse($event->start_date)->format('d.m.Y') }}
                            - {{ \Illuminate\Support\Carbon::parse($event->end_date)->format('d.m.Y') }}</td>
                        @canany(['update', 'delete'], $event)
                            <td>
                                @can('update', $event)
                                    <a href="{{ route('events.edit', $event) }}" class="btn btn-warning btn-sm">Редактировать</a>
                                @endcan
                                @can('delete', $event)
                                    <button class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal"
                                            data-action="{{ route('events.destroy', $event) }}">
                                        Удалить
                                    </button>
                                @endcan
                            </td>
                        @endcanany
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $events->links() }}
    @endif

    @include('partials.delete-modal')
    @include('partials.date-error-modal')

@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Скрипт для модалки удаления
            var deleteModal = document.getElementById('deleteModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var action = button.getAttribute('data-action');
                    var form = document.getElementById('deleteForm');
                    form.action = action;
                });
            }

            // Скрипт для живого поиска мероприятий
            var searchInput = document.getElementById('search-input');
            if (searchInput) {
                let typingTimer;
                const doneTypingInterval = 500; // задержка 0.5 секунды

                searchInput.addEventListener('input', function () {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(() => {
                        this.form.submit();
                    }, doneTypingInterval);
                });
            }

            // Проверка диапазона дат при отправке формы фильтра мероприятий
            const eventFilterForm = document.getElementById('filter-form');
            if (eventFilterForm) {
                eventFilterForm.addEventListener('submit', function(e) {
                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;

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

