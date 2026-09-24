<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_is_installable(): void
    {
        $response = $this->get('/manifest.webmanifest');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json; charset=UTF-8');

        $manifest = $response->json();

        $this->assertSame('Warung Hebat', $manifest['short_name']);
        $this->assertSame('/', $manifest['start_url']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#1A130D', $manifest['theme_color']);

        $icons = collect($manifest['icons']);
        $this->assertTrue($icons->contains(fn (array $icon): bool => $icon['sizes'] === '192x192'));
        $this->assertTrue($icons->contains(fn (array $icon): bool => $icon['sizes'] === '512x512'));
        $this->assertTrue($icons->contains(fn (array $icon): bool => ($icon['purpose'] ?? '') === 'maskable'));
    }

    public function test_service_worker_exists_with_offline_caching(): void
    {
        $path = public_path('sw.js');

        $this->assertFileExists($path);

        $worker = (string) file_get_contents($path);

        $this->assertStringContainsString('warunghebat-v1', $worker);
        $this->assertStringContainsString('/offline', $worker);
    }

    public function test_offline_page_is_reachable(): void
    {
        $this->get(route('pwa.offline'))
            ->assertOk()
            ->assertSee('Sinyal lagi hilang');
    }

    public function test_layout_links_the_manifest_and_install_banner(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee('/manifest.webmanifest', false)
            ->assertSee('apple-touch-icon', false)
            ->assertSee('pwa-install', false);

        $bundle = (string) glob(public_path('build/assets/app-*.js'))[0];

        $this->assertStringContainsString('serviceWorker', (string) file_get_contents($bundle));
    }
}
