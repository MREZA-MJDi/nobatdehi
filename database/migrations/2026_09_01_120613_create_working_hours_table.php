<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();

            $table->foreignId('salon_id')
                ->constrained('salons')
                ->cascadeOnDelete();

            /*
            | 0 = شنبه
            | 1 = یکشنبه
            | 2 = دوشنبه
            | 3 = سه‌شنبه
            | 4 = چهارشنبه
            | 5 = پنجشنبه
            | 6 = جمعه
            */
            $table->unsignedTinyInteger('day_of_week');

            $table->time('start_time')
                ->nullable();

            $table->time('end_time')
                ->nullable();

            $table->boolean('is_closed')
                ->default(false);

            /*
            | Order of intervals inside a day.
            |
            | Example:
            | 0 => 09:00 - 13:00
            | 1 => 14:00 - 22:00
            */
            $table->unsignedSmallInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->index([
                'salon_id',
                'day_of_week',
                'sort_order',
            ]);

            $table->index([
                'salon_id',
                'is_closed',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
