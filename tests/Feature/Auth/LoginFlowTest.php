<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider portalRoles
     */
    public function test_successful_login_reaches_the_correct_role_portal(
        UserRole $role,
        string $routeName
    ): void {
        $user = User::factory()->create([
            'role' => $role,
            'phone' => '09' . fake()->numerify('#########'),
            'password' => 'password123',
        ]);

        $response = $this->post(route('login.store'), [
            'phone' => $user->phone,
            'password' => 'password123',
        ]);

        $response
            ->assertRedirect(route($routeName))
            ->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_discover_exposes_login_and_registration_to_guests(): void
    {
        $response = $this->get(route('salons.discover'));

        $response
            ->assertOk()
            ->assertSee(route('login'))
            ->assertSee(route('register'))
            ->assertSee('ورود')
            ->assertSee('شروع کن');
    }

    public function test_barber_accounts_are_not_allowed_to_login_as_portal_users(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::BARBER,
            'phone' => '09' . fake()->numerify('#########'),
            'password' => 'password123',
        ]);

        $response = $this->post(route('login.store'), [
            'phone' => $user->phone,
            'password' => 'password123',
        ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    /**
     * @return array<string, array{UserRole, string}>
     */
    public static function portalRoles(): array
    {
        return [
            'super admin' => [
                UserRole::SUPER_ADMIN,
                'admin.dashboard',
            ],
            'salon owner' => [
                UserRole::SALON_OWNER,
                'salon.dashboard',
            ],
            'customer' => [
                UserRole::CUSTOMER,
                'customer.dashboard',
            ],
        ];
    }
}
