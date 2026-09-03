<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('salon_id')
                ->after('id')
                ->constrained('salons')
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->after('salon_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('booking_id')
                ->after('customer_id')
                ->unique()
                ->constrained('bookings')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('rating')
                ->after('booking_id');

            $table->text('comment')
                ->nullable()
                ->after('rating');

            $table->boolean('is_published')
                ->default(true)
                ->index()
                ->after('comment');

            $table->index([
                'salon_id',
                'is_published',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign([
                'booking_id',
            ]);

            $table->dropForeign([
                'customer_id',
            ]);

            $table->dropForeign([
                'salon_id',
            ]);

            $table->dropColumn([
                'salon_id',
                'customer_id',
                'booking_id',
                'rating',
                'comment',
                'is_published',
            ]);
        });
    }
};
