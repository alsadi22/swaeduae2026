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
        /** Contact form topic “data rights” — erasure / subject-access; defaults to support when unset. */
        'privacy' => env('MAIL_PRIVACY_ADDRESS') ?: env('MAIL_SUPPORT_ADDRESS', 'support@swaeduae.ae'),
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

    /*
    |--------------------------------------------------------------------------
    | Security headers (Phase 4 / roadmap H2)
    |--------------------------------------------------------------------------
    |
    | When CSP_REPORT_ONLY is a non-empty string, it is sent as the
    | Content-Security-Policy-Report-Only header on every web response.
    | Start with Report-Only + a report endpoint before enforcing CSP.
    |
    */
    'security' => [
        'csp_report_only' => env('CSP_REPORT_ONLY'),
        /**
         * When true, users with admin or super-admin must complete TOTP setup and enter a code after password sign-in.
         * Tests set ADMIN_TWO_FACTOR_REQUIRED=false in phpunit.xml.
         */
        'admin_two_factor_required' => filter_var(env('ADMIN_TWO_FACTOR_REQUIRED', false), FILTER_VALIDATE_BOOLEAN),
    ],

];
