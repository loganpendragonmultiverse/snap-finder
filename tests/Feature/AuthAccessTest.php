<?php

namespace Tests\Feature;

use App\Models\SnapProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_listing_creation(): void
    {
        $this->get('/profiles/create')
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_user_is_forbidden_from_dashboard(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_public_pages_send_security_headers(): void
    {
        $this->get('/directory')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_age_gate_rejects_external_return_destination(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get('/age-gate?return=https://attacker.example/steal')
            ->assertOk()
            ->assertViewHas('returnTo', route('directory.index', ['nsfw' => 1]));
    }

    public function test_logout_is_post_only(): void
    {
        $this->get('/logout')->assertStatus(405);
    }

    public function test_suspended_account_loses_an_existing_authenticated_session(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_suspended' => true,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_save_and_unsave_public_listing(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $profile = SnapProfile::create([
            'user_id' => $owner->id,
            'display_name' => 'Beta Tester',
            'snap_username' => 'betatester',
            'headline' => 'Testing discovery',
            'bio' => 'A safe public test listing.',
            'looking_for' => 'Friends',
            'category' => 'Friends',
            'status' => 'approved',
            'is_approved' => true,
        ]);

        $this->actingAs($viewer)->post(route('directory.save', $profile))->assertRedirect();
        $this->assertDatabaseHas('saved_snap_profiles', ['user_id' => $viewer->id, 'snap_profile_id' => $profile->id]);

        $this->actingAs($viewer)->post(route('directory.save', $profile))->assertRedirect();
        $this->assertDatabaseMissing('saved_snap_profiles', ['user_id' => $viewer->id, 'snap_profile_id' => $profile->id]);
    }
}
