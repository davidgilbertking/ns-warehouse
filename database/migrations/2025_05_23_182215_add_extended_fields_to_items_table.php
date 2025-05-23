<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Общее
            $table->text('mechanics')->nullable();
            $table->text('scalability')->nullable();
            $table->decimal('client_price', 10, 2)->nullable();

            // Для ОП
            $table->json('op_media')->nullable(); // массив ссылок или файлов
            $table->text('branding_options')->nullable();
            $table->text('adaptation_options')->nullable();
            $table->text('op_price')->nullable();

            // Для реализации
            $table->json('real_media')->nullable();
            $table->text('construction_description')->nullable();
            $table->text('contractor')->nullable();
            $table->text('production_cost')->nullable();
            $table->text('change_history')->nullable();
            $table->text('consumables')->nullable();
            $table->text('implementation_comments')->nullable();
            $table->text('mounting')->nullable();
            $table->text('storage_features')->nullable();
            $table->text('design_links')->nullable();

            // История
            $table->text('event_history')->nullable();
            $table->json('event_media')->nullable();

            // Перенос существующего поля
            $table->string('storage_place')->nullable(); // если это то же самое, что storage_location, можно объединить
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                                   'mechanics',
                                   'scalability',
                                   'client_price',
                                   'op_media',
                                   'branding_options',
                                   'adaptation_options',
                                   'op_price',
                                   'real_media',
                                   'construction_description',
                                   'contractor',
                                   'production_cost',
                                   'change_history',
                                   'consumables',
                                   'implementation_comments',
                                   'mounting',
                                   'storage_features',
                                   'design_links',
                                   'event_history',
                                   'event_media',
                                   'storage_place',
                               ]);
        });
    }
};
