<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_otps', function (Blueprint $table) {
            $table->id();

            $table->string('phone', 20)->index();

            $table->string('purpose', 30)->index();

            $table->string('code');

            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamp('expires_at');

            $table->timestamp('consumed_at')->nullable();

            $table->timestamp('sent_at')->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index([
                'phone',
                'purpose',
                'consumed_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_otps');
    }
};
