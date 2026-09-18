<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_inbound_messages', function (Blueprint $table) {
            $table->id();

            $table->string('provider_message_id', 150)
                ->nullable()
                ->unique();

            $table->string('from_phone', 30);

            $table->string('body', 500);

            $table->foreignId('booking_id')
                ->nullable()
                ->constrained('bookings')
                ->nullOnDelete();

            $table->string('action', 20)
                ->nullable();

            $table->string('status', 30)
                ->default('received');

            $table->string('error', 500)
                ->nullable();

            $table->json('payload')
                ->nullable();

            $table->timestamp('processed_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'from_phone',
                'status',
            ]);

            $table->index([
                'booking_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_inbound_messages');
    }
};
