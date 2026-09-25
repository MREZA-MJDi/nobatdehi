<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_favorites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('salon_id')
                ->constrained('salons')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['customer_id', 'salon_id'],
                'salon_favorites_customer_salon_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_favorites');
    }
};
