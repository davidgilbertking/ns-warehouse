@extends('layouts.app')

@section('content')
    <h1>Создать {{ mb_strtolower($entityName) }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-md-10">
            <form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="depth" value="{{ $depth }}">
                <h4 class="mt-4">Общее</h4>
                @include('items.partials.general-fields')

                @if ($depth === 0)

                    @include('items.partials.composition-fields')

                    <h4 class="mt-4">Для ОП</h4>
                @include('items.partials.op-fields')
                @endif

                <h4 class="mt-4">Для реализации</h4>
                @include('items.partials.real-fields')

                <h4 class="mt-4">История проведения</h4>
                @include('items.partials.history-fields')

                <button type="submit"
                        class="btn btn-primary mt-4"
                        onclick="this.disabled = true; this.innerText = 'Сохраняется…'; this.form.submit();">
                    Создать предмет
                </button>
                <a href="{{ route('items.index') }}" class="btn btn-secondary mt-4 ms-2">Назад</a>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
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

            $('#subitem_ids').select2({
                placeholder: "Выберите предметы для состава",
                width: '100%'
            });
        });
    </script>
@endsection
