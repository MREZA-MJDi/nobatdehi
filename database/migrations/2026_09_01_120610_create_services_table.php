<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('salon_id')
                ->constrained('salons')
                ->cascadeOnDelete();

            $table->string('name', 150);

            $table->text('description')
                ->nullable();

            $table->unsignedSmallInteger(
                'duration_minutes'
            )->default(60);

            $table->unsignedBigInteger(
                'price'
            )->default(0);

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->unsignedInteger(
                'sort_order'
            )->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'salon_id',
                'is_active',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
