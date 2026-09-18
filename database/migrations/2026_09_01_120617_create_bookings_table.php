<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('salon_id')
                ->constrained('salons')
                ->cascadeOnDelete();

            $table->foreignId('barber_id')
                ->constrained('barbers')
                ->restrictOnDelete();

            $table->foreignId('service_id')
                ->constrained('services')
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->date('booking_date');

            $table->time('start_time');

            $table->time('end_time');

            $table->unsignedBigInteger('price')
                ->default(0);

            $table->string('status', 30)
                ->default('pending');

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->index([
                'salon_id',
                'booking_date',
                'status',
            ]);

            $table->index([
                'barber_id',
                'booking_date',
                'start_time',
            ]);

            $table->index([
                'customer_id',
                'booking_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
