<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->foreign('manager_barber_id')
                ->references('id')
                ->on('barbers')
                ->nullOnDelete();
        });
    }


    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            $table->dropForeign([
                'manager_barber_id',
            ]);
        });
    }
};
