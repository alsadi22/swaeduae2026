<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Publishes institutional IA + legal pages so public routes resolve via CMS (see InstitutionalPageController).
 */
class InstitutionalCmsPageSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = User::query()->value('id');
        $publishedAt = now()->subDay();

        foreach ($this->rows() as $row) {
            CmsPage::query()->updateOrCreate(
                [
                    'slug' => $row['slug'],
                    'locale' => $row['locale'],
                ],
                array_merge($row, [
                    'status' => CmsPage::STATUS_PUBLISHED,
                    'published_at' => $publishedAt,
                    'author_id' => $authorId,
                ])
            );
        }
    }

    /**
     * @return list<array{slug: string, locale: string, title: string, meta_description: string|null, excerpt: string|null, body: string}>
     */
    private function rows(): array
    {
        return [
            [
                'slug' => 'about',
                'locale' => 'en',
                'title' => 'About',
                'meta_description' => 'SwaedUAE Association for Culture and Community Empowerment — who we are, mission, vision, and values.',
                'excerpt' => 'We bring together volunteers, partners, and local leaders to deliver cultural and community initiatives with transparency and measurable impact.',
                'body' => <<<'MD'
We bring together volunteers, partners, and local leaders to deliver cultural and community initiatives with transparency and measurable impact.

## Mission

Detailed mission copy will be managed in the CMS.

## Vision

Vision statement — CMS.

## Values

Values list — CMS.

See the **[Leadership](/about#leadership)** section for board structure and profiles.
MD,
            ],
            [
                'slug' => 'about',
                'locale' => 'ar',
                'title' => 'من نحن',
                'meta_description' => 'جمعية سواعد الإمارات للثقافة وتمكين المجتمع — من نحن، الرسالة، الرؤية، والقيم.',
                'excerpt' => 'نجمع المتطوعين والشركاء والقادة المحليين لتقديم مبادرات ثقافية ومجتمعية بشفافية وأثر قابل للقياس.',
                'body' => <<<'MD'
نجمع المتطوعين والشركاء والقادة المحليين لتقديم مبادرات ثقافية ومجتمعية بشفافية وأثر قابل للقياس.

## الرسالة

نص الرسالة التفصيلي — نظام إدارة المحتوى.

## الرؤية

بيان الرؤية — نظام إدارة المحتوى.

## القيم

قائمة القيم — نظام إدارة المحتوى.

اطلع على قسم **[القيادة](/about#leadership)** لهيكل المجلس والملفات التعريفية.
MD,
            ],
            [
                'slug' => 'programs',
                'locale' => 'en',
                'title' => 'Programs & initiatives',
                'meta_description' => 'SwaedUAE programs and community initiatives.',
                'excerpt' => 'Programs and initiatives will be listed here with filters once the CMS is live.',
                'body' => <<<'MD'
Programs and initiatives will be listed here with filters once the CMS is live. Below is a preview of how initiative summaries can appear in Markdown.

## Initiative 1

Summary and CTA will come from the CMS.

## Initiative 2

Summary and CTA will come from the CMS.

## Initiative 3

Summary and CTA will come from the CMS.

## Initiative 4

Summary and CTA will come from the CMS.

## Initiative 5

Summary and CTA will come from the CMS.

## Initiative 6

Summary and CTA will come from the CMS.
MD,
            ],
            [
                'slug' => 'programs',
                'locale' => 'ar',
                'title' => 'البرامج والمبادرات',
                'meta_description' => 'برامج ومبادرات سواعد الإمارات المجتمعية.',
                'excerpt' => 'ستُدرج البرامج والمبادرات هنا مع المرشحات عند تشغيل نظام إدارة المحتوى.',
                'body' => <<<'MD'
ستُدرج البرامج والمبادرات هنا مع المرشحات عند تشغيل نظام إدارة المحتوى. فيما يلي معاينة لكيفية عرض الملخصات في Markdown.

## المبادرة 1

الملخص ودعوة العمل ستأتي من نظام إدارة المحتوى.

## المبادرة 2

الملخص ودعوة العمل ستأتي من نظام إدارة المحتوى.

## المبادرة 3

الملخص ودعوة العمل ستأتي من نظام إدارة المحتوى.

## المبادرة 4

الملخص ودعوة العمل ستأتي من نظام إدارة المحتوى.

## المبادرة 5

الملخص ودعوة العمل ستأتي من نظام إدارة المحتوى.

## المبادرة 6

الملخص ودعوة العمل ستأتي من نظام إدارة المحتوى.
MD,
            ],
            [
                'slug' => 'events',
                'locale' => 'en',
                'title' => 'Events',
                'meta_description' => 'SwaedUAE public events and volunteer-linked activities.',
                'excerpt' => 'Public events and volunteer-linked activities will be published here with dates and locations.',
                'body' => <<<'MD'
Public events and volunteer-linked activities will be published here with dates and locations.

## Demo community event (attendance test)

Local seed only — replace with CMS events.

[Browse volunteer opportunities](/volunteer/opportunities)
MD,
            ],
            [
                'slug' => 'events',
                'locale' => 'ar',
                'title' => 'الفعاليات',
                'meta_description' => 'فعاليات سواعد الإمارات العامة والأنشطة المرتبطة بالتطوع.',
                'excerpt' => 'ستُنشر هنا الفعاليات العامة والأنشطة المرتبطة بالتطوع مع التواريخ والمواقع.',
                'body' => <<<'MD'
ستُنشر هنا الفعاليات العامة والأنشطة المرتبطة بالتطوع مع التواريخ والمواقع.

## فعالية مجتمعية تجريبية (اختبار الحضور)

بذور محلية فقط — تُستبدل بفعاليات نظام إدارة المحتوى.

[استعرض فرص التطوع](/volunteer/opportunities)
MD,
            ],
            [
                'slug' => 'media',
                'locale' => 'en',
                'title' => 'Media center',
                'meta_description' => 'News, reports, and media from SwaedUAE.',
                'excerpt' => 'News, reports, and gallery content will be managed through the admin CMS.',
                'body' => <<<'MD'
News, reports, and gallery content will be managed through the admin CMS.

## News

News and announcements will appear here once the media center is connected to the CMS.

## Reports

Downloadable reports and resources — CMS.
MD,
            ],
            [
                'slug' => 'media',
                'locale' => 'ar',
                'title' => 'المركز الإعلامي',
                'meta_description' => 'الأخبار والتقارير والإعلام من سواعد الإمارات.',
                'excerpt' => 'تُدار الأخبار والتقارير ومعرض الصور من لوحة الإدارة.',
                'body' => <<<'MD'
تُدار الأخبار والتقارير ومعرض الصور من لوحة الإدارة.

## الأخبار

ستظهر الأخبار والإعلانات هنا عند ربط المركز الإعلامي بنظام إدارة المحتوى.

## التقارير

تقارير وموارد قابلة للتنزيل — نظام إدارة المحتوى.
MD,
            ],
            [
                'slug' => 'partners',
                'locale' => 'en',
                'title' => 'Partners',
                'meta_description' => 'Partners and supporters of SwaedUAE.',
                'excerpt' => 'We thank our partners and supporters who make community impact possible.',
                'body' => <<<'MD'
We thank our partners and supporters who make community impact possible.

Partner and sponsor logos will be showcased here once the media module is connected.
MD,
            ],
            [
                'slug' => 'partners',
                'locale' => 'ar',
                'title' => 'الشركاء',
                'meta_description' => 'شركاء وداعمو سواعد الإمارات.',
                'excerpt' => 'نشكر شركاءنا وداعمينا الذين يمكنون الأثر المجتمعي.',
                'body' => <<<'MD'
نشكر شركاءنا وداعمينا الذين يمكنون الأثر المجتمعي.

ستُعرض هنا شعارات الشركاء والرعاة عند ربط وحدة الإعلام.
MD,
            ],
            [
                'slug' => 'faq',
                'locale' => 'en',
                'title' => 'FAQ',
                'meta_description' => 'Frequently asked questions about SwaedUAE and volunteering.',
                'excerpt' => 'Frequently asked questions will be organized by category.',
                'body' => <<<'MD'
Frequently asked questions will be organized by category. Contact us for anything not covered here.

### How do I check in to an event?

Use the signed link or QR from your coordinator while logged in, allow GPS, and tap Check in on the attendance page.
MD,
            ],
            [
                'slug' => 'faq',
                'locale' => 'ar',
                'title' => 'الأسئلة الشائعة',
                'meta_description' => 'أسئلة شائعة حول سواعد الإمارات والتطوع.',
                'excerpt' => 'ستُرتَّب الأسئلة الشائعة حسب الفئة.',
                'body' => <<<'MD'
ستُرتَّب الأسئلة الشائعة حسب الفئة. تواصل معنا لما لا يُغطّى هنا.

### كيف أسجّل حضوري في فعالية؟

استخدم الرابط الموقّع أو رمز الاستجابة من منسّقك وأنت مسجّل الدخول، اسمح بالموقع، ثم اضغط تسجيل الدخول في صفحة الحضور.
MD,
            ],
            [
                'slug' => 'youth-councils',
                'locale' => 'en',
                'title' => 'Youth Councils',
                'meta_description' => 'SwaedUAE and Youth Councils in the UAE — youth participation, community empowerment, and how to contact our association.',
                'excerpt' => 'Youth participation and leadership are part of our wider mission in culture and community empowerment, within the UAE national youth ecosystem.',
                'body' => <<<'MD'
## Youth councils in context

Across the United Arab Emirates, youth councils and related structures support **youth voice**, **leadership**, and **community engagement** as part of the national landscape for developing young people. Approaches can vary across emirate, local, sectoral, and other frameworks.

This page offers **institutional context for visitors to SwaedUAE** and is **not** a reproduction of any third-party website.

## Our involvement

**SwaedUAE Association for Culture and Community Empowerment** connects with the Youth Councils **ecosystem** in keeping with our mandate in **culture** and **community empowerment**. We support youth participation where it aligns with our programmes and values.

Programmes, partnerships, and any formal roles should be described **only** with wording approved for public use. Editors may update this section in the CMS as official lines are confirmed.

## Why this matters

Investing in youth leadership and volunteer culture strengthens **community resilience** and long-term social impact—priorities that match our institutional goals.

## Alignment with our mission

Youth engagement complements our work to empower communities and enrich cultural life. See **[About](/about)** and **[Programs & initiatives](/programs)** for the wider association narrative.

_Contact details for Youth Councils inquiries appear in the contact section below the main article on the public page._

_Set **OG / share image** in the CMS to show an optional banner image under the title on this page._
MD,
            ],
            [
                'slug' => 'youth-councils',
                'locale' => 'ar',
                'title' => 'مجالس الشباب',
                'meta_description' => 'سواعد الإمارات ومجالس الشباب في دولة الإمارات — مشاركة الشباب، تمكين المجتمع، ووسائل التواصل.',
                'excerpt' => 'مشاركة الشباب والقيادة جزء من رسالتنا الأوسع في الثقافة وتمكين المجتمع، ضمن المنظومة الوطنية للشباب.',
                'body' => <<<'MD'
## مجالس الشباب في سياقها

في دولة الإمارات، تدعم مجالس الشباب والهياكل الشبابية المرتبطة بها **صوت الشباب** و**القيادة** و**الانخراط المجتمعي** ضمن المشهد الوطني لتنمية الشباب. وتختلف الصيغ بين المستويات والقطاعات.

تقدّم هذه الصفحة **سياقاً مؤسسياً لزوار موقع سواعد الإمارات** وليست نسخاً من أي موقع خارجي.

## مساهمتنا وارتباطنا

**جمعية سواعد الإمارات للثقافة وتمكين المجتمع** تتصل ب**منظومة مجالس الشباب** بما يتوافق مع اختصاصنا في **الثقافة** و**تمكين المجتمع**. ندعم مشاركة الشباب حيث تنسجم مع برامجنا وقيمنا.

يجب أن تُوصف البرامج والشراكات وأي أدوار رسمية **بلغة معتمدة للنشر العام** فقط. يمكن للمحررين تحديث هذا القسم في نظام إدارة المحتوى عند اعتماد الصياغات.

## لماذا يهم الأمر

الاستثمار في قيادات الشباب وثقافة التطوع يعزز **مرونة المجتمع** والأثر الاجتماعي على المدى الطويل — بما يتماشى مع أهدافنا المؤسسية.

## الانسجام مع رسالتنا

يُكمِل انخراط الشباب عملنا على تمكين المجتمعات وإثراء الحياة الثقافية. اطلع على **[من نحن](/about)** و**[البرامج والمبادرات](/programs)** للسرد الأوسع للجمعية.

_تفاصيل التواصل بخصوص مجالس الشباب تظهر في قسم الاتصال أسفل المقال في الصفحة العامة._

_يمكن ضبط **صورة المشاركة / OG** في نظام إدارة المحتوى لعرض صورة اختيارية أسفل العنوان._
MD,
            ],
            [
                'slug' => 'terms',
                'locale' => 'en',
                'title' => 'Terms of use',
                'meta_description' => 'Terms of use for the SwaedUAE website.',
                'excerpt' => null,
                'body' => <<<'MD'
Terms of use placeholder — replace with counsel-approved legal text before production launch.
MD,
            ],
            [
                'slug' => 'terms',
                'locale' => 'ar',
                'title' => 'شروط الاستخدام',
                'meta_description' => 'شروط استخدام موقع سواعد الإمارات.',
                'excerpt' => null,
                'body' => <<<'MD'
نص احتياطي لشروط الاستخدام — يُستبدل بنص قانوني معتمد قبل الإطلاق.
MD,
            ],
            [
                'slug' => 'privacy',
                'locale' => 'en',
                'title' => 'Privacy policy',
                'meta_description' => 'Privacy policy for the SwaedUAE website and volunteer platform.',
                'excerpt' => null,
                'body' => <<<'MD'
Privacy policy placeholder — describe data collection, Zoho mail, attendance GPS, retention, and data rights (export/delete) per your PRD.
MD,
            ],
            [
                'slug' => 'privacy',
                'locale' => 'ar',
                'title' => 'سياسة الخصوصية',
                'meta_description' => 'سياسة الخصوصية لموقع سواعد الإمارات ومنصة التطوع.',
                'excerpt' => null,
                'body' => <<<'MD'
نص احتياطي لسياسة الخصوصية — يصف جمع البيانات وبريد زوهو وموقع الحضور والاحتفاظ وحقوق التصدير/الحذف وفق مواصفاتكم.
MD,
            ],
            [
                'slug' => 'cookies',
                'locale' => 'en',
                'title' => 'Cookie policy',
                'meta_description' => 'Cookie policy for the SwaedUAE website.',
                'excerpt' => null,
                'body' => <<<'MD'
Cookie policy placeholder — describe essential vs analytics cookies, consent, and how visitors can manage preferences before production launch.
MD,
            ],
            [
                'slug' => 'cookies',
                'locale' => 'ar',
                'title' => 'سياسة ملفات تعريف الارتباط',
                'meta_description' => 'سياسة ملفات تعريف الارتباط لموقع سواعد الإمارات.',
                'excerpt' => null,
                'body' => <<<'MD'
نص احتياطي لسياسة ملفات تعريف الارتباط — يصف الضرورية والتحليلات والموافقة وكيفية إدارة التفضيلات قبل الإطلاق.
MD,
            ],
        ];
    }
}
