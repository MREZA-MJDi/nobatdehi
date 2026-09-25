<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('salons')) {
            return;
        }

        $legacy = DB::table('salons')
            ->where('code', 'SALON-NOBAN')
            ->first();

        if ($legacy && !DB::table('salons')->where('code', 'SALON-MAJID')->exists()) {
            DB::table('salons')
                ->where('id', $legacy->id)
                ->update([
                    'code' => 'SALON-MAJID',
                    'name' => 'آرایشگاه مجید',
                    'slug' => 'arayeshgah-majid',
                    'description' => 'آرایشگاه مجید با خدمات تخصصی مو، میکاپ و زیبایی.',
                    'updated_at' => now(),
                ]);
        }

        $salons = DB::table('salons')
            ->where(function ($query) {
                $query
                    ->whereNull('slug')
                    ->orWhere('slug', '')
                    ->orWhere('slug', '/');
            })
            ->orderBy('id')
            ->get(['id', 'name', 'code']);

        foreach ($salons as $salon) {
            $base = Str::slug((string) $salon->name);

            if ($base === '') {
                $base = Str::slug((string) $salon->code);
            }

            if ($base === '') {
                $base = 'salon-' . $salon->id;
            }

            $slug = $base;
            $suffix = 2;

            while (DB::table('salons')->where('slug', $slug)->where('id', '!=', $salon->id)->exists()) {
                $slug = $base . '-' . $suffix;
                $suffix++;
            }

            DB::table('salons')
                ->where('id', $salon->id)
                ->update([
                    'slug' => $slug,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('salons')) {
            return;
        }

        DB::table('salons')
            ->where('code', 'SALON-MAJID')
            ->where('slug', 'arayeshgah-majid')
            ->update([
                'code' => 'SALON-NOBAN',
                'name' => 'سالن زیبایی نوبان',
                'slug' => '/',
                'updated_at' => now(),
            ]);
    }
};
