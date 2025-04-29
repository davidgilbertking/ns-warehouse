@extends('layouts.app')

@section('content')
    @can('create', App\Models\User::class)
        <a href="{{ route('admin.users.create') }}" class="btn btn-success mb-3">Создать пользователя</a>
    @endcan
    <h1>Пользователи</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($users->count())
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th class="text-center">Действия</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role }}</td>
                        <td class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">Редактировать</a>
                            @if (auth()->id() !== $user->id)
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                        data-action="{{ route('admin.users.destroy', $user) }}">
                                    Удалить
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    @else
        <div class="alert alert-warning">
            Нет пользователей для отображения.
        </div>
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
        });
    </script>
@endsection
