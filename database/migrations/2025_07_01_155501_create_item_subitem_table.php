<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('item_subitem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('subitem_id')->constrained('items')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['item_id', 'subitem_id']); // Запрещаем дубли
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_subitem');
    }
};
