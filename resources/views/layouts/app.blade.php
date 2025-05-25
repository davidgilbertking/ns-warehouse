<!doctype html>
<html lang="ru" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NS Warehouse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .nav-link.active {
            font-weight: bold;
            color: #0d6efd;
        }

        /* Select2 под Bootstrap 5 */
        .select2-container--default .select2-selection--multiple {
            background-color: var(--bs-body-bg);
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            min-height: calc(2.25rem + 2px);
            padding: 0.375rem 0.75rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.25rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #e9ecef;
            border: none;
            border-radius: 0.25rem;
            padding: 0.25rem 0.5rem 0.25rem 0.75rem; /* ← левый отступ больше */
            color: #212529;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            position: relative;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: absolute;
            left: 0.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-weight: bold;
            font-size: 1rem;
            line-height: 1;
            cursor: pointer;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .select2-container--default .select2-search--inline .select2-search__field {
            width: auto !important;
            flex-grow: 1;
            margin-top: 2px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg mb-4" style="background-color: var(--bs-body-bg);">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">NS Warehouse</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Переключить навигацию">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}"
                       href="{{ route('items.index') }}">
                        Предметы
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                       href="{{ route('products.index') }}">
                        Тэги
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}"
                       href="{{ route('events.index') }}">
                        Мероприятия
                    </a>
                </li>

                @auth
                    @if (auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                               href="{{ route('admin.users.index') }}">
                                Пользователи
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('logs.index') ? 'active' : '' }}"
                               href="{{ route('logs.index') }}">
                                Логи
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-2">
                    <button id="toggle-theme" class="btn btn-outline-secondary btn-sm">🌙</button>
                </li>
                @auth
                    <li class="nav-item">
                        <span class="nav-link">{{ auth()->user()->name }}</span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-link nav-link" type="submit">Выход</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link">Войти</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    @yield('content')
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Тёмная тема: запоминаем выбор в localStorage
    document.addEventListener('DOMContentLoaded', function () {
        const html = document.documentElement;
        const toggleButton = document.getElementById('toggle-theme');

        // Установить тему из localStorage при загрузке
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            html.setAttribute('data-bs-theme', savedTheme);
            updateIcon(savedTheme);
        }

        toggleButton.addEventListener('click', function () {
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });

        function updateIcon(theme) {
            if (theme === 'dark') {
                toggleButton.innerHTML = '☀️';
            } else {
                toggleButton.innerHTML = '🌙';
            }
        }

        const datepickers = [
            '#available_from',
            '#available_to',
            '#start_date',
            '#end_date',
            '#date'
        ];

        datepickers.forEach(function (selector) {
            const el = document.querySelector(selector);
            if (el) {
                flatpickr(el, {
                    dateFormat: "Y-m-d",
                    locale: "ru",
                    allowInput: true,
                    disableMobile: true,
                    onReady: function (selectedDates, dateStr, instance) {
                        if (instance.input.hasAttribute('placeholder')) {
                            instance._input.setAttribute('placeholder', instance.input.getAttribute('placeholder'));
                        }
                    }
                });
            }
        });
    });
</script>

@yield('scripts')
</body>
</html>
