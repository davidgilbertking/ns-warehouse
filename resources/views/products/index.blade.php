@extends('layouts.app')

@section('content')
    @can('create', App\Models\Product::class)
        <a href="{{ route('products.create') }}" class="btn btn-success mb-3">Создать продукт</a>
    @endcan

    <h1>Список продуктов</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif


    @if ($products->isEmpty())
        <div class="alert alert-warning">
            Нет продуктов для отображения.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Название</th>
                    @if (auth()->user() && auth()->user()->role === 'admin')
                        <th>Действия</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>
                            <a href="{{ route('products.show', $product) }}">
                                {{ $product->name }}
                            </a>
                        </td>
                        @canany(['update', 'delete'], $product)
                            <td>
                                <div class="d-flex gap-2">
                                    @can('update', $product)
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm">Редактировать</a>
                                    @endcan
                                    @can('delete', $product)
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-action="{{ route('products.destroy', $product) }}">
                                            Удалить
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        @endcanany
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $products->links() }}
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
