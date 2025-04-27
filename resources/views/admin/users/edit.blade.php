@extends('layouts.app')

@section('content')
    <h1>Редактировать пользователя: {{ $user->name }}</h1>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Имя</label>
                    <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Роль</label>
                    <select name="role" class="form-select" required>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Администратор</option>
                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Пользователь</option>
                        <option value="viewer" {{ $user->role === 'viewer' ? 'selected' : '' }}>Наблюдатель</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Новый пароль (если нужно)</label>
                    <input type="password" name="password" class="form-control">
                    <small class="text-muted">Если оставить пустым — пароль останется прежним.</small>
                </div>

                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
            </form>
        </div>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Назад</a>

@endsection
