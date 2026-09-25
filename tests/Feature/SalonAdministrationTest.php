<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Barber;
use App\Models\Salon;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use Database\Seeders\SalonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SalonAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_salon_with_owner_login_credentials(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name' => 'Super Admin',
            'phone' => '09129990000',
            'email' => 'admin@example.test',
            'password' => 'admin-password',
            'role' => UserRole::SUPER_ADMIN,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.salons.store'),
            [
                'name' => 'سالن تست تحویل',
                'manager_name' => 'مدیر تست',
                'manager_phone' => '09128887766',
                'manager_password' => 'OwnerPass123',
                'manager_password_confirmation' => 'OwnerPass123',
                'email' => 'salon@example.test',
                'province' => 'تهران',
                'city' => 'تهران',
                'district' => 'ونک',
                'address' => 'آدرس تست',
                'is_active' => '1',
            ]
        );

        $response->assertRedirect();

        $owner = User::query()
            ->where('phone', '09128887766')
            ->firstOrFail();

        $salon = Salon::query()
            ->where('owner_id', $owner->id)
            ->firstOrFail();

        $this->assertSame('سالن تست تحویل', $salon->name);
        $this->assertSame(UserRole::SALON_OWNER, $owner->role);
        $this->assertTrue($owner->must_change_password);
        $this->assertTrue(Hash::check('OwnerPass123', $owner->password));
    }

    public function test_reseeding_preserves_existing_owner_password_and_edited_working_hours(): void
    {
        Storage::fake('public');

        $this->seed(SalonSeeder::class);

        $owner = User::query()
            ->where('phone', '09121112233')
            ->firstOrFail();

        $salon = Salon::query()
            ->where('code', 'SALON-MAJID')
            ->firstOrFail();

        $owner->update([
            'password' => 'RealOwnerPassword',
            'must_change_password' => false,
        ]);

        $service = $salon->services()->firstOrFail();
        $service->update(['price' => 999999]);

        $barber = $salon->barbers()->firstOrFail();
        $barber->update(['specialty' => 'تخصص واقعی']);

        $hour = $salon->workingHours()
            ->where('day_of_week', 0)
            ->firstOrFail();

        $hour->update([
            'start_time' => '11:00',
            'end_time' => '17:00',
            'is_closed' => false,
        ]);

        $this->seed(SalonSeeder::class);

        $owner->refresh();
        $salon->refresh();
        $service->refresh();
        $barber->refresh();
        $hour->refresh();

        $this->assertTrue(Hash::check('RealOwnerPassword', $owner->password));
        $this->assertFalse($owner->must_change_password);
        $this->assertSame(999999, (int) $service->price);
        $this->assertSame('تخصص واقعی', $barber->specialty);
        $this->assertSame('11:00', substr((string) $hour->start_time, 0, 5));
        $this->assertSame('17:00', substr((string) $hour->end_time, 0, 5));

        $this->assertSame(
            1,
            $salon->services()->where('name', $service->name)->count()
        );
    }

    public function test_owner_sees_dashboard_return_on_own_public_salon(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'phone' => '09127776655',
            'password' => 'password',
            'role' => UserRole::SALON_OWNER,
        ]);

        $salon = Salon::create([
            'name' => 'سالن من',
            'slug' => 'my-salon',
            'code' => 'MY-SALON',
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('public.salons.show', $salon))
            ->assertOk()
            ->assertSee('داشبورد سالن');
    }
}
