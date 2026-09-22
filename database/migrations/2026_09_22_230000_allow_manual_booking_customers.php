<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->change();

            $table->string('customer_name', 120)
                ->nullable()
                ->after('customer_id');

            $table->string('customer_phone', 20)
                ->nullable()
                ->after('customer_name');

            $table->boolean('is_manual')
                ->default(false)
                ->after('customer_phone')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['is_manual']);
            $table->dropColumn([
                'customer_name',
                'customer_phone',
                'is_manual',
            ]);

            $table->foreignId('customer_id')
                ->nullable(false)
                ->change();
        });
    }
};
