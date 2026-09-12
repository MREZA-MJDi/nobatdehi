<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('salon_id')
                ->constrained('salons')
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('rating');

            $table->text('comment')
                ->nullable();

            $table->boolean('is_published')
                ->default(true)
                ->index();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | A customer can review a booking only once.
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'booking_id',
                'customer_id',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Useful indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'salon_id',
                'is_published',
            ]);

            $table->index([
                'customer_id',
                'is_published',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
