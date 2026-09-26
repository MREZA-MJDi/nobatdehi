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

    public function test_super_admin_can_soft_delete_salon_and_remove_branding_files(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name' => 'Super Admin',
            'phone' => '09129991111',
            'email' => 'admin-delete@example.test',
            'password' => 'admin-password',
            'role' => UserRole::SUPER_ADMIN,
        ]);

        $owner = User::create([
            'name' => 'Salon Owner',
            'phone' => '09128881111',
            'password' => 'owner-password',
            'role' => UserRole::SALON_OWNER,
        ]);

        $salon = Salon::create([
            'name' => 'سالن قابل حذف',
            'slug' => 'deletable-salon',
            'code' => 'DELETE-SALON',
            'owner_id' => $owner->id,
            'logo_path' => 'salons/logos/delete-logo.jpg',
            'cover_path' => 'salons/covers/delete-cover.jpg',
            'qr_code_path' => 'salons/qr/delete-salon.svg',
            'is_active' => true,
        ]);

        Storage::disk('public')->put($salon->logo_path, 'logo');
        Storage::disk('public')->put($salon->cover_path, 'cover');
        Storage::disk('public')->put($salon->qr_code_path, 'qr');

        $response = $this->actingAs($admin)->delete(
            route('admin.salons.destroy', $salon)
        );

        $response
            ->assertRedirect(route('admin.salons.index'))
            ->assertSessionHas('success', 'سالن با موفقیت حذف شد.');

        $this->assertSoftDeleted('salons', [
            'id' => $salon->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'role' => UserRole::SALON_OWNER->value,
        ]);

        Storage::disk('public')->assertMissing($salon->logo_path);
        Storage::disk('public')->assertMissing($salon->cover_path);
        Storage::disk('public')->assertMissing($salon->qr_code_path);
    }

    public function test_salon_owner_cannot_delete_a_salon_from_admin_area(): void
    {
        $owner = User::create([
            'name' => 'Salon Owner',
            'phone' => '09128882222',
            'password' => 'owner-password',
            'role' => UserRole::SALON_OWNER,
        ]);

        $salon = Salon::create([
            'name' => 'سالن مالک',
            'slug' => 'owner-salon',
            'code' => 'OWNER-SALON',
            'owner_id' => $owner->id,
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->delete(route('admin.salons.destroy', $salon))
            ->assertForbidden();

        $this->assertDatabaseHas('salons', [
            'id' => $salon->id,
            'deleted_at' => null,
        ]);
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
        $this->assertSame(450000, (int) $service->price);
        $this->assertSame('رنگ و لایت', $barber->specialty);
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
