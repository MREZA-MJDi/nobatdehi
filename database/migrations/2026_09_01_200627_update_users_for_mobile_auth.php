<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | Mobile Number
            |--------------------------------------------------------------------------
            */

            $table->string('phone', 20)
                ->unique()
                ->after('name');


            $table->timestamp('phone_verified_at')
                ->nullable()
                ->after('phone');


            /*
            |--------------------------------------------------------------------------
            | Email becomes optional
            |--------------------------------------------------------------------------
            */

            $table->string('email')
                ->nullable()
                ->change();


            /*
            |--------------------------------------------------------------------------
            | Password is no longer required
            |--------------------------------------------------------------------------
            */

            $table->string('password')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique([
                'phone',
            ]);

            $table->dropColumn([
                'phone',
                'phone_verified_at',
            ]);

            $table->string('email')
                ->nullable(false)
                ->change();

            $table->string('password')
                ->nullable(false)
                ->change();
        });
    }
};
