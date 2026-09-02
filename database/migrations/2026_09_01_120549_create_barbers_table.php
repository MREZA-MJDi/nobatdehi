<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barbers', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Salon
            |--------------------------------------------------------------------------
            */

            $table->foreignId('salon_id')
                ->constrained('salons')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            $table->string('name', 150);

            $table->string('phone', 30)
                ->nullable();

            $table->text('bio')
                ->nullable();

            $table->string('specialty', 150)
                ->nullable();

            $table->string('image_path')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                ->default(true)
                ->index();


            /*
            |--------------------------------------------------------------------------
            | Timestamps / Soft Delete
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barbers');
    }
};
