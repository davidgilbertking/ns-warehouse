@extends('layouts.app')

@section('content')
    <h1>{{ $item->name }}</h1>

    @if(!auth()->user()->isViewer() && !auth()->user()->isGuest())
        <a href="{{ route('items.edit', $item) }}" class="btn btn-warning mb-3">Редактировать</a>
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

    @if ($item->videos->count())
        <h5 class="mt-4">Видео:</h5>
        <div class="row">
            @foreach ($item->videos as $video)
                <div class="col-md-6 mb-3">
                    <video controls class="w-100 rounded">
                        <source src="{{ asset('storage/' . $video->path) }}" type="video/mp4">
                        Ваш браузер не поддерживает видео.
                    </video>
                </div>
            @endforeach
        </div>
    @endif

    <h4>Общее</h4>
    @include('items.partials.general-show')
    @if (!auth()->user()?->isGuest())
    <div class="accordion mb-3" id="accordionSections">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOp">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOp" aria-expanded="false" aria-controls="collapseOp">
                    Для ОП
                </button>
            </h2>
            <div id="collapseOp" class="accordion-collapse collapse" aria-labelledby="headingOp" data-bs-parent="#accordionSections">
                <div class="accordion-body">
                    @include('items.partials.op-show')
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingReal">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReal" aria-expanded="false" aria-controls="collapseReal">
                    Для реализации
                </button>
            </h2>
            <div id="collapseReal" class="accordion-collapse collapse" aria-labelledby="headingReal" data-bs-parent="#accordionSections">
                <div class="accordion-body">
                    @include('items.partials.real-show')
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingHistory">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHistory" aria-expanded="false" aria-controls="collapseHistory">
                    История проведения
                </button>
            </h2>
            <div id="collapseHistory" class="accordion-collapse collapse" aria-labelledby="headingHistory" data-bs-parent="#accordionSections">
                <div class="accordion-body">
                    @include('items.partials.history-show')
                </div>
            </div>
        </div>
    </div>
    @endif
    <a href="{{ route('items.index') }}" class="btn btn-secondary mt-3">Назад</a>

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
                img.addEventListener('click', function () {
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
