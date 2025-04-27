@extends('layouts.app')

@section('content')
    <h1>{{ $item->name }}</h1>

    @if(auth()->user()->role !== 'viewer')
        <a href="{{ route('items.edit', $item) }}" class="btn btn-warning  mb-3">Редактировать</a>
    @endif

    @if ($item->images->count())
        <div class="row">
            @foreach ($item->images as $image)
                <div class="col-md-3 mb-3">
                    <img src="{{ asset('storage/' . $image->path) }}" alt="Фото" class="img-fluid rounded zoomable-image" style="cursor: zoom-in;">
                </div>
            @endforeach
        </div>
    @endif

    @if ($item->description)
        <p><strong>Описание:</strong> {{ $item->description }}</p>
    @endif

    @if ($item->size)
        <p><strong>Размер:</strong> {{ $item->size }}</p>
    @endif

    @if ($item->material)
        <p><strong>Материал:</strong> {{ $item->material }}</p>
    @endif

    @if ($item->supplier)
        <p><strong>Подрядчик / Магазин:</strong> {{ $item->supplier }}</p>
    @endif

    @if ($item->storage_location)
        <p><strong>Место хранения:</strong> {{ $item->storage_location }}</p>
    @endif
    <p><strong>Количество на складе:</strong> {{ $item->quantity }}</p>


    <a href="{{ route('items.index') }}" class="btn btn-secondary">Назад</a>

    <!-- Модалка для увеличения -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <img id="modalImage" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const images = document.querySelectorAll('.zoomable-image');
            images.forEach(img => {
                img.addEventListener('click', function() {
                    const modal = document.getElementById('imageModal');
                    const modalImg = document.getElementById('modalImage');
                    modalImg.src = this.src;
                    const modalInstance = new bootstrap.Modal(modal);
                    modalInstance.show();
                });
            });
        });
    </script>
@endsection
