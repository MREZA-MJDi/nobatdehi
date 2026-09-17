<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)
                ->default(10)
                ->after('price');

            $table->unsignedBigInteger('commission_amount')
                ->default(0)
                ->after('commission_rate');

            $table->string('commission_recipient', 20)
                ->default('salon')
                ->after('commission_amount');

            $table->string('commission_status', 20)
                ->default('pending')
                ->after('commission_recipient');

            $table->unsignedBigInteger('confirmed_by')
                ->nullable()
                ->after('status');

            $table->timestamp('confirmed_at')
                ->nullable()
                ->after('confirmed_by');

            $table->boolean('priority_overridden')
                ->default(false)
                ->after('confirmed_at');

            $table->text('priority_override_reason')
                ->nullable()
                ->after('priority_overridden');

            $table->text('status_note')
                ->nullable()
                ->after('notes');

            $table->foreign('confirmed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index([
                'barber_id',
                'booking_date',
                'status',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropIndex([
                'bookings_barber_id_booking_date_status_created_at_index',
            ]);

            $table->dropColumn([
                'commission_rate',
                'commission_amount',
                'commission_recipient',
                'commission_status',
                'confirmed_by',
                'confirmed_at',
                'priority_overridden',
                'priority_override_reason',
                'status_note',
            ]);
        });
    }
};
