<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
class TailEndBetaTest extends TestCase {
 use RefreshDatabase;
 public function test_public_pages_are_standalone_without_author_integrations(): void {
  foreach(['/','/directory','/login','/register','/privacy','/terms','/contact'] as $path)
   $this->get($path)->assertOk()->assertDontSee('googletagmanager')->assertDontSee('lab-feedback.php')->assertDontSee('contact-intake.php');
 }
 public function test_fresh_install_has_no_profiles_or_users(): void {
  $this->seed(\Database\Seeders\CatalogSeeder::class);
  $this->assertDatabaseCount('snap_categories',10);
  $this->assertDatabaseCount('users',0);$this->assertDatabaseCount('snap_profiles',0);
  $this->get('/directory')->assertOk();
 }
 public function test_account_export_is_private(): void {
  $this->get('/account/export')->assertRedirect(route('login'));
  $u=User::factory()->create();
  $this->actingAs($u)->get('/account/export')->assertOk();
 }
}
