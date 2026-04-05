<?php

return [

    'domain' => env('APP_DOMAIN', 'swaeduae.ae'),

    'mail' => [
        /** Youth Councils programme inquiries (dedicated public page). */
        'youth_councils' => env('MAIL_YOUTH_COUNCILS_ADDRESS', 'youthcouncils@swaeduae.ae'),
        /** Public contact form, partnerships, general / media inquiries. */
        'info' => env('MAIL_INFO_ADDRESS', 'info@swaeduae.ae'),
        /** Volunteer / org user help: login, registration, attendance, certificates. */
        'support' => env('MAIL_SUPPORT_ADDRESS', 'support@swaeduae.ae'),
        /** Internal operational alerts (disputes, new org registration copy, etc.). */
        'admin_alerts' => env('MAIL_ADMIN_ADDRESS'),
        /** Same as admin_alerts; used when a volunteer opens an attendance dispute. */
        'staff_disputes' => env('MAIL_ADMIN_ADDRESS'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Open Graph / Twitter image (Phase C7)
    |--------------------------------------------------------------------------
    |
    | Used on the home page, media hub, programs fallback, and as a fallback for CMS
    | pages that omit og_image. Prefer a 1200×630 HTTPS URL or a path starting with /
    | (resolved with url()). When unset, some pages may omit og:image.
    |
    */
    'default_og_image_url' => env('DEFAULT_OG_IMAGE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Public partner logos (Phase C5)
    |--------------------------------------------------------------------------
    |
    | Shown on the home page and on /partners when non-empty.
    | Each item: label (EN), optional label_ar, url, logo (https URL or /path under public).
    | Leave empty to show the bilingual placeholder line from lang files.
    |
    */
    'home_partners' => [
        // ['label' => 'Example partner', 'label_ar' => 'شريك', 'url' => 'https://example.org', 'logo' => '/images/partners/example.svg'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gallery page — report / document links (Phase C4)
    |--------------------------------------------------------------------------
    |
    | Shown on /gallery when non-empty. Each item: label, optional label_ar, url (https or path).
    |
    */
    'document_downloads' => [
        // ['label' => 'Annual report (PDF)', 'label_ar' => 'التقرير السنوي', 'url' => 'https://example.org/report.pdf'],
    ],

];
