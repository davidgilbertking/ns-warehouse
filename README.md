# NS Warehouse

Добро пожаловать в проект **NS Warehouse**!

Это веб-приложение для управления предметами, тэгами и мероприятиями на складе, разработанное на **Laravel 11** с поддержкой авторизации, ролей пользователей, логирования действий и мобильной адаптивности.

---

## 🚀 Быстрый старт

### Установка

1. Клонировать репозиторий:

```bash
git clone https://github.com/davidgilbertking/ns-warehouse.git
cd ns-warehouse
```

2. Установить зависимости:

```bash
composer install
npm install && npm run build # (если используется vite)
```

3. Создать файл `.env`:

```bash
cp .env.example .env
```

4. Сгенерировать ключ приложения:

```bash
php artisan key:generate
```

5. Настроить `.env` (подключить базу данных)

```plaintext
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=warehouse_db
DB_USERNAME=david
DB_PASSWORD=
```

6. Запустить миграции и заполнить тестовыми данными:

```bash
php artisan migrate:fresh --seed
```

7. Запустить сервер:

```bash
php artisan serve
```


---

## 🔐 Данные для входа (по умолчанию)

- **Администратор**  
  Email: `admin@local`  
  Пароль: `password`

- **Наблюдатель**  
  Email: `viewer@local`  
  Пароль: `password`


---

## 📚 Основной функционал

- Управление **предметами** (CRUD, фото, поиск, экспорт в CSV)
- Управление **тэгами** (наборы предметов)
- Управление **мероприятиями** (бронирование предметов на даты)
- Проверка доступности предметов при создании и редактировании
- Авторизация и роли пользователей (**Admin**, **User**, **Viewer**)
- Логирование действий в системе
- Темная тема 🌙 (переключение прямо на сайте)
- Полностью адаптивный интерфейс 📱💻


---

## 🛠️ Технологии

- Laravel 11
- PHP 8+
- Bootstrap 5.3
- JavaScript (Vanilla JS)
- PostgreSQL или MySQL (на выбор)


---

## 📂 Структура проекта

- `app/Models/` — модели
- `app/Http/Controllers/` — контроллеры
- `resources/views/` — шаблоны Blade
- `routes/web.php` — роутинг
- `database/seeders/` — сидеры тестовых данных


---

## 📜 Лицензия

Проект доступен для свободного использования в некоммерческих целях.


---

> Разработано с любовью ❤️ в 2025 году

---


### Контакты разработчика

```plaintext
Telegram: @davidgilbertking
Email: david_kazaryan@mail.ru
```

---
