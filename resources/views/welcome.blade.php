@extends('layouts.app')

@section('content')
    <div class="text-center mt-5">
        <h1 class="display-4 fade-in">Добро пожаловать в NS Warehouse!</h1>
        <p class="lead mt-4 fade-in" style="animation-delay: 0.5s;">
            Здесь вы можете управлять складом, мероприятиями и продуктами быстро и удобно.
        </p>
        <a href="{{ route('items.index') }}" class="btn btn-primary btn-lg mt-4 fade-in" style="animation-delay: 1s;">
            Перейти в Каталог предметов
        </a>
    </div>
@endsection

@section('scripts')
    <style>
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 1s forwards;
        }

        .fade-in[style*="animation-delay"] {
            animation-delay: inherit;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
