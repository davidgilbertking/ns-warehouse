@extends('layouts.app')

@section('content')
    <h1>Создать пользователя</h1>

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
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Имя</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Пароль</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Подтвердите пароль</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Роль</label>
                    <select name="role" class="form-select" required>
                        <option value="user">Пользователь</option>
                        <option value="admin">Администратор</option>
                        <option value="viewer">Наблюдатель</option>
                        <option value="guest">Гость</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Создать</button>
            </form>
        </div>
    </div>

    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mt-3">Назад</a>
@endsection
