<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private const SECTIONS = [
        'admin.dashboard',
        'admin.users',
        'admin.warungs',
        'admin.products',
        'admin.articles',
        'admin.finance',
        'admin.payments',
        'admin.orders',
        'admin.withdrawals',
        'admin.payment-methods',
        'admin.inquiries',
        'admin.settings',
    ];

    public function test_every_backoffice_section_renders_for_an_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (self::SECTIONS as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    public function test_mobile_topbar_exposes_the_navigation_drawer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.products'))
            ->assertOk()
            ->assertSee('id="admin-nav-btn"', false)
            ->assertSee('aria-controls="admin-drawer"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('id="admin-drawer"', false)
            ->assertSee('id="admin-drawer-backdrop"', false)
            ->assertSee('id="admin-nav-close"', false);
    }

    public function test_topbar_names_the_current_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.articles'))
            ->assertOk()
            ->assertSee('<span class="block text-[13px] font-extrabold truncate">Artikel</span>', false);

        $this->actingAs($admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('<span class="block text-[13px] font-extrabold truncate">Pengaturan Platform</span>', false);
    }

    public function test_drawer_and_sidebar_link_to_every_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        foreach (self::SECTIONS as $route) {
            $response->assertSee('href="'.route($route).'"', false);
        }

        $response->assertSee('href="'.route('home').'"', false);
    }

    public function test_nav_marks_the_active_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.products'))
            ->assertOk()
            ->assertSee('class="flex items-center gap-3 px-4 py-3 rounded-2xl transition bg-white text-ink-900"', false);
    }
}
