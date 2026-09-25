<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('working_hours', function (Blueprint $table): void {
            $table->foreignId('barber_id')
                ->nullable()
                ->after('salon_id')
                ->constrained('barbers')
                ->nullOnDelete();

            $table->index([
                'salon_id',
                'barber_id',
                'day_of_week',
                'sort_order',
            ], 'working_hours_scope_day_sort_index');
        });
    }

    public function down(): void
    {
        Schema::table('working_hours', function (Blueprint $table): void {
            $table->dropIndex('working_hours_scope_day_sort_index');
            $table->dropForeign(['barber_id']);
            $table->dropColumn('barber_id');
        });
    }
};
