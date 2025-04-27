<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('size')->nullable();
            $table->string('material')->nullable();
            $table->string('supplier')->nullable(); // подрядчик/магазин
            $table->string('storage_location')->nullable(); // место хранения
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['size', 'material', 'supplier', 'storage_location']);
        });
    }
};
