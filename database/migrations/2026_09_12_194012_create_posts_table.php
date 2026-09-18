<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
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
            | Performer
            |--------------------------------------------------------------------------
            |
            | barber_id:
            | The barber who performed the work.
            |
            | performed_by_owner:
            | The salon owner performed the work himself.
            |
            */

            $table->foreignId('barber_id')
                ->nullable()
                ->constrained('barbers')
                ->nullOnDelete();

            $table->boolean('performed_by_owner')
                ->default(false)
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Related service
            |--------------------------------------------------------------------------
            */

            $table->foreignId('service_id')
                ->nullable()
                ->constrained('services')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Media type
            |--------------------------------------------------------------------------
            */

            $table->string('type', 20);

            /*
            |--------------------------------------------------------------------------
            | Media files
            |--------------------------------------------------------------------------
            */

            $table->string('media_path');

            $table->string('thumbnail_path')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Post content
            |--------------------------------------------------------------------------
            */

            $table->string('title', 150)
                ->nullable();

            $table->text('caption')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Visibility / ordering
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->unsignedInteger('sort_order')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Statistics
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('views_count')
                ->default(0);

            $table->unsignedBigInteger('likes_count')
                ->default(0);

            $table->unsignedBigInteger('comments_count')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Timestamps / soft deletes
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'salon_id',
                'is_active',
                'sort_order',
            ]);

            $table->index([
                'salon_id',
                'type',
                'is_active',
            ]);

            $table->index([
                'salon_id',
                'barber_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
