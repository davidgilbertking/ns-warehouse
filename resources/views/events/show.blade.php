@extends('layouts.app')

@section('content')
    <h1>{{ $event->name }}</h1>

    @if(!auth()->user()->isViewer() && !auth()->user()->isGuest())
        <div class="d-flex gap-2 mb-4">
            <a href="{{ route('events.edit', $event) }}" class="btn btn-warning">Редактировать мероприятие</a>
            <a href="{{ route('events.clone', $event) }}" class="btn btn-light">Клонировать мероприятие</a>
        </div>
    @endif

    <p><strong>Период:</strong> {{ $event->start_date->format('d.m.Y') }} — {{ $event->end_date->format('d.m.Y') }}</p>
    <p><strong>Создатель:</strong> {{ $event->user->name }}</p>

    <a href="{{ route('events.exportItems', $event) }}" class="btn btn-info mb-3">Экспортировать список предметов в CSV</a>

    <h3>Зарезервированные предметы:</h3>
    @if ($event->reservations->isEmpty())
        <p>Нет зарезервированных предметов.</p>
    @else
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Название предмета</th>
                <th>Количество</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($event->reservations as $reservation)
                <tr>
                    <td>
                        @if ($reservation->item)
                            <a href="{{ route('items.show', $reservation->item) }}">
                                {{ $reservation->item->name }}
                            </a>
                        @else
                            <span class="text-danger">[Удалённый предмет]</span>
                        @endif
                    </td>
                    <td>{{ $reservation->quantity }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('events.index') }}" class="btn btn-secondary">Назад</a>
@endsection
