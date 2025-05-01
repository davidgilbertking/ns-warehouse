@extends('layouts.app')

@section('content')
    <h1>Журнал действий</h1>

    <form method="GET" action="{{ route('logs.index') }}" class="row g-2 mb-4">
        <div class="col-md-2">
            <input type="text" name="user" class="form-control" placeholder="Пользователь"
                   value="{{ request('user') }}">
        </div>
        <div class="col-md-2">
            <input type="text" name="action" class="form-control" placeholder="Действие"
                   value="{{ request('action') }}">
        </div>
        <div class="col-md-2">
            <input type="text" name="entity_type" class="form-control" placeholder="Объект"
                   value="{{ request('entity_type') }}">
        </div>
        <div class="col-md-2">
            <input type="text" name="description" class="form-control" placeholder="Описание"
                   value="{{ request('description') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="date" class="form-control" placeholder="Дата" value="{{ request('date') }}">
        </div>
        <div class="col-md-1 mt-2 d-grid">
            <a href="{{ route('logs.index') }}" class="btn btn-secondary  ">Очистить</a>
        </div>
        <div class="col-md-1 d-grid">
            <button type="submit" class="btn btn-primary  ">Фильтр</button>
        </div>
    </form>

    @if ($logs->isEmpty())
        <div class="alert alert-warning">
            Нет логов для отображения.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>Действие</th>
                    <th>Объект</th>
                    <th>Описание</th>
                    <th>Дата и время (МСК)</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td>{{ $log->user?->name ?? 'Неизвестный пользователь' }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->created_at->timezone('Europe/Moscow')->format('d.m.Y H:i') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $logs->onEachSide(1)->links('pagination::bootstrap-5') }}
    @endif
@endsection
