<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YouthCouncilsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_youth_councils_page_renders_with_lang_fallback_when_cms_missing(): void
    {
        $this->get(route('youth-councils'))
            ->assertOk()
            ->assertSee('Youth Councils', false)
            ->assertSee('youthcouncils@swaeduae.ae', false)
            ->assertSee(route('about'), false)
            ->assertSee(route('programs.index'), false);
    }

    public function test_youth_councils_page_uses_published_cms_when_present(): void
    {
        CmsPage::query()->create([
            'slug' => 'youth-councils',
            'locale' => 'en',
            'title' => 'Youth Councils CMS Title',
            'meta_description' => 'Meta for youth councils test.',
            'excerpt' => 'Custom excerpt for hero.',
            'body' => '## From CMS

Unique marker YCMS12345.',
            'status' => CmsPage::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        $this->get(route('youth-councils'))
            ->assertOk()
            ->assertSee('Youth Councils CMS Title', false)
            ->assertSee('Custom excerpt for hero.', false)
            ->assertSee('YCMS12345', false)
            ->assertSee('youthcouncils@swaeduae.ae', false);
    }
}
