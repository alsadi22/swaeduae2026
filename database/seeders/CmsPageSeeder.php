<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Database\Seeder;

class CmsPageSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->first();

        $pages = [
            [
                'slug' => 'community-charter',
                'locale' => 'en',
                'title' => 'Community charter',
                'meta_description' => 'How SwaedUAE works with volunteers, partners, and the public.',
                'excerpt' => 'Principles for transparent, bilingual, and accountable community engagement.',
                'body' => <<<'MD'
## Our commitment

SwaedUAE exists to strengthen cultural life and community empowerment in the UAE. This charter summarizes how we work with volunteers, partners, and the public.

### What you can expect

- **Bilingual communication** — Arabic and English content with clear, respectful language.
- **Verified volunteering** — Attendance and hours are tied to check-in integrity (QR, GPS, and audit trails).
- **Privacy-aware operations** — Personal data is handled according to our privacy policy and applicable law.

### What we ask

- Accurate profiles and honest participation when you volunteer.
- Respectful engagement with staff, partners, and other volunteers.

_Full legal terms and privacy details are linked in the site footer._
MD,
                'status' => CmsPage::STATUS_PUBLISHED,
                'published_at' => now()->subDay(),
                'show_on_home' => true,
            ],
            [
                'slug' => 'community-charter',
                'locale' => 'ar',
                'title' => 'ميثاق المجتمع',
                'meta_description' => 'كيف تعمل سواعد الإمارات مع المتطوعين والشركاء والجمهور.',
                'excerpt' => 'مبادئ الشفافية والثنائية اللغوية والمساءلة في العمل المجتمعي.',
                'body' => <<<'MD'
## التزامنا

تعمل **سواعد الإمارات** على تعزيز الحياة الثقافية والتمكين المجتمعي في دولة الإمارات. يلخص هذا الميثاق طريقة تعاملنا مع المتطوعين والشركاء والجمهور.

### ما يمكن توقعه

- **تواصل ثنائي اللغة** — محتوى عربي وإنجليزي بلغة واضحة ومحترمة.
- **تطوع موثّق** — ربط الحضور والساعات بنزاهة تسجيل الدخول (رمز الاستجابة السريعة والموقع والسجلات).
- **خصوصية** — معالجة البيانات وفق سياسة الخصوصية والقوانين المعمول بها.

### ما نطلبه

- ملفات دقيقة ومشاركة صادقة عند التطوع.
- احترام الموظفين والشركاء والمتطوعين الآخرين.

_تفاصيل قانونية كاملة متاحة عبر روابط التذييل._
MD,
                'status' => CmsPage::STATUS_PUBLISHED,
                'published_at' => now()->subDay(),
                'show_on_home' => true,
            ],
        ];

        foreach ($pages as $row) {
            CmsPage::query()->updateOrCreate(
                [
                    'slug' => $row['slug'],
                    'locale' => $row['locale'],
                ],
                array_merge($row, ['author_id' => $author?->id])
            );
        }

        CmsPage::query()->updateOrCreate(
            [
                'slug' => 'draft-sample',
                'locale' => 'en',
            ],
            [
                'title' => 'Draft sample (not public)',
                'body' => 'This page must not appear on the public site.',
                'status' => CmsPage::STATUS_DRAFT,
                'published_at' => null,
                'author_id' => $author?->id,
            ]
        );

        $this->call(InstitutionalCmsPageSeeder::class);
    }
}
