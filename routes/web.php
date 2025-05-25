<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ItemImageController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ItemVideoController;

// Главная страница
Route::get('/', function () {
    return view('welcome');
})->middleware('auth')->name('home');

// Страница приветствия
Route::get('/welcome', function () {
    return view('welcome');
});

// Дашборд
Route::get('/dashboard', function () {
    return redirect('/');
})->middleware(['auth', 'verified'])->name('dashboard');

// Аутентификация
require __DIR__ . '/auth.php';

// Группа маршрутов для авторизованных пользователей
Route::middleware('auth')->group(function () {
    // Профиль
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Предметы
    Route::get('/items/export', [ItemController::class, 'export'])->name('items.export');
    Route::delete('/items/images/{image}', [ItemImageController::class, 'destroy'])->name('items.images.destroy');
    Route::post('/items/{item}/images', [ItemImageController::class, 'store'])->name('items.images.store');
    Route::resource('items', ItemController::class);

    // Видео для предметов
    Route::post('/items/{item}/videos', [ItemVideoController::class, 'store'])->name('items.videos.store');
    Route::delete('/items/videos/{video}', [ItemVideoController::class, 'destroy'])->name('items.videos.destroy');

    // Мероприятия
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::get('/events/{event}/clone', [EventController::class, 'cloneEvent'])->name('events.clone');
    Route::get('/events/{event}/export-items', [EventController::class, 'exportItems'])->name('events.exportItems');
    Route::post('/events/check-availability', [EventController::class, 'checkAvailability']);
    Route::get('/api/availability', function (\Illuminate\Http\Request $request) {
        return app(\App\Http\Controllers\EventController::class)->getAvailableQuantities($request);
    });
    Route::get('/api/get-available-quantities', [EventController::class, 'getAvailableQuantities']);
    Route::resource('events', EventController::class)->except(['create']);

    // Продукты
    Route::get('/products/{product}/export', [ProductController::class, 'export'])->name('products.export');
    Route::put('/products/{product}/update-items', [ProductController::class, 'updateItems'])->name(
        'products.updateItems'
    );
    Route::get('/products/{product}/items', [ProductController::class, 'items'])->name('products.items');
    Route::get('/api/products/{product}/items', function (\App\Models\Product $product) {
        return $product->items->map(function ($item) {
            return [
                'id'       => $item->id,
                'name'     => $item->name,
                'quantity' => $item->pivot->quantity,
            ];
        });
    });
    Route::resource('products', ProductController::class);

    // Логи
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');

    // Админ-панель пользователей
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']
        );
    });
});
