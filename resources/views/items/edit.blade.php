@extends('layouts.app')

@section('content')
    <h1>Редактировать предмет: {{ $item->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($item->images->count())
        <h5>Текущие фото:</h5>
        <div class="row mb-3">
            @foreach ($item->images as $image)
                <div class="col-md-3 mb-3 position-relative">
                    <img src="{{ asset('storage/' . $image->path) }}" alt="Фото" class="img-fluid rounded">

                    <button type="button"
                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                            style="z-index: 2;"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteModal"
                            data-action="{{ route('items.images.destroy', $image) }}">
                        ×
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    <h5>Добавить новое фото:</h5>
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <form method="POST" action="{{ route('items.images.store', $item) }}" enctype="multipart/form-data" class="mb-4">
                @csrf
                <div class="input-group">
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                    <button type="submit" class="btn btn-success">Загрузить фото</button>
                </div>
            </form>
        </div>
    </div>

    @if ($item->videos->count())
        <h5>Текущие видео:</h5>
        <div class="row mb-3">
            @foreach ($item->videos as $video)
                <div class="col-md-6 mb-3 position-relative">
                    <video controls class="w-100 rounded">
                        <source src="{{ asset('storage/' . $video->path) }}" type="video/mp4">
                        Ваш браузер не поддерживает видео.
                    </video>
                </div>
            @endforeach
        </div>
    @endif

    <h5>Добавить новое видео:</h5>
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <form method="POST" action="{{ route('items.videos.store', $item) }}" enctype="multipart/form-data" class="mb-4">
                @csrf
                <div class="input-group">
                    <input type="file" id="videos" name="videos[]" class="form-control"
                           accept="video/mp4,video/webm,video/ogg,video/quicktime" multiple required>
                    <button type="submit" class="btn btn-success">Загрузить видео</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-12 col-md-10">
            <form method="POST" action="{{ route('items.update', $item) }}">
                @csrf
                @method('PUT')

                <h4 class="mt-4">Общее</h4>
                @include('items.partials.general-fields-edit')

                <h4 class="mt-4">Для ОП</h4>
                @include('items.partials.op-fields-edit')

                <h4 class="mt-4">Для реализации</h4>
                @include('items.partials.real-fields-edit')

                <h4 class="mt-4">История проведения</h4>
                @include('items.partials.history-fields-edit')

                <button type="submit" class="btn btn-primary mt-4">Сохранить изменения</button>
                <a href="{{ route('items.show', $item) }}" class="btn btn-secondary mt-4 ms-2">Назад</a>
            </form>
        </div>
    </div>

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

        function addMediaField(containerId, inputName) {
            const wrapper = document.getElementById(containerId);
            const input = document.createElement('input');
            input.type = 'text';
            input.name = inputName;
            input.className = 'form-control mb-2';
            input.placeholder = 'Ссылка на медиа';
            wrapper.appendChild(input);
        }

        document.addEventListener('DOMContentLoaded', function () {
            $('#product_ids').select2({
                placeholder: "Выберите тэги",
                width: '100%'
            });
        });
    </script>
@endsection
