<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salons', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            // Public immutable code used by URL / QR
            $table->string('code', 40)
                ->unique();

            $table->text('description')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Owner / Account
            |--------------------------------------------------------------------------
            |
            | The user account that controls this salon.
            |
            */

            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */

            $table->string('phone', 30)
                ->nullable();

            $table->string('email')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Branding
            |--------------------------------------------------------------------------
            */

            $table->string('logo_path')
                ->nullable();

            $table->string('cover_path')
                ->nullable();

            $table->string('primary_color', 20)
                ->nullable();

            $table->string('secondary_color', 20)
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            $table->string('province', 100)
                ->nullable();

            $table->string('city', 100)
                ->nullable();

            $table->string('district', 100)
                ->nullable();

            $table->text('address')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Map
            |--------------------------------------------------------------------------
            */

            $table->decimal('latitude', 10, 7)
                ->nullable();

            $table->decimal('longitude', 10, 7)
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | QR
            |--------------------------------------------------------------------------
            */

            $table->string('qr_code_path')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Created By
            |--------------------------------------------------------------------------
            |
            | Super Admin who created this salon.
            |
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


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
        Schema::dropIfExists('salons');
    }
};
