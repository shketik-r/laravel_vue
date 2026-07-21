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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->integer('age');

            // === ДОБАВЬТЕ ЭТУ СТРОКУ ===
            // nullable() нужен, чтобы старые клиенты без категории не вызвали ошибку
            // constrained() связывает поле с таблицей categories в MySQL
            // cascadeOnDelete() удалит клиентов, если удалить саму категорию
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
