@extends('layouts.app')

@section('content')
    <h1>Продукт: {{ $product->name }}</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(!auth()->user()->isViewer())
        <div class="d-flex gap-2 mb-4">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">Редактировать продукт</a>
            @endif
            <a href="{{ route('events.create', ['product_id' => $product->id]) }}" class="btn btn-primary">Создать
                мероприятие из этого продукта</a>
        </div>
    @endif

    <h4>Состав продукта:</h4>

    @if ($product->items->isEmpty())
        <div class="alert alert-warning">
            В этом продукте пока нет предметов.
        </div>
    @else
        <ul class="list-group mb-3">
            @foreach ($product->items as $item)
                <li class="list-group-item">
                    <a href="{{ route('items.show', $item) }}">{{ $item->name }}</a> —
                    {{ $item->pivot->quantity }} шт.
                </li>
            @endforeach
        </ul>
    @endif


    <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">Назад к списку продуктов</a>
@endsection
