<?php

use App\Http\Controllers\Public\AcceptOrganizationInvitationController;
use App\Http\Controllers\Public\CmsPageController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\ExternalNewsPublicController;
use App\Http\Controllers\Public\GalleryPublicController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LeadershipEntryController;
use App\Http\Controllers\Public\InstitutionalPageController;
use App\Http\Controllers\Public\MediaAtomFeedController;
use App\Http\Controllers\Public\MediaHubController;
use App\Http\Controllers\Public\OrganizationRegistrationController;
use App\Http\Controllers\Public\ProgramsIndexController;
use App\Http\Controllers\Public\PublicEventController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\SupportController;
use App\Http\Controllers\Public\VolunteerOpportunitiesAtomFeedController;
use App\Http\Controllers\Public\YouthCouncilsController;
use App\Http\Controllers\Volunteer\VolunteerHubController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::permanentRedirect('/volunteerplatform', '/volunteer');

Route::get('/register/organization', [OrganizationRegistrationController::class, 'show'])
    ->middleware('guest')
    ->name('register.organization');
Route::post('/register/organization', [OrganizationRegistrationController::class, 'store'])
    ->middleware(['guest', 'throttle:guest-organization-register'])
    ->name('register.organization.store');

Route::get('/organization/join/{token}', [AcceptOrganizationInvitationController::class, 'show'])
    ->middleware(['throttle:30,1'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->name('organization.invitation.accept');

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin',
        'Disallow: /dashboard',
        'Disallow: /profile',
        'Disallow: /organization/',
        'Disallow: /attendance/',
        'Disallow: /login',
        'Disallow: /forgot-password',
        'Disallow: /reset-password',
        'Disallow: /verify-email',
        'Disallow: /confirm-password',
        '',
        'Sitemap: '.route('sitemap', [], true),
    ];

    return response(implode("\n", $lines), 200)
        ->header('Content-Type', 'text/plain; charset=UTF-8')
        ->header('Cache-Control', 'public, max-age=3600');
})->name('robots');

Route::get('/humans.txt', static function (): Response {
    $appUrl = rtrim((string) config('app.url'), '/');
    $lines = [
        '# SwaedUAE — humans.txt',
        'Site: '.$appUrl,
        'Volunteer hub: '.$appUrl.'/volunteer',
        'Volunteer registration: '.route('register.volunteer', [], true),
        'Organization registration: '.route('register.organization', [], true),
        'Contact & support: '.route('contact.show', [], true),
        'News feed: '.route('feed', [], true),
        'Sitemap: '.route('sitemap', [], true),
        '',
        'Built with Laravel. Security disclosure: '.$appUrl.'/.well-known/security.txt',
    ];

    return response(implode("\n", $lines)."\n", 200)
        ->header('Content-Type', 'text/plain; charset=UTF-8')
        ->header('Cache-Control', 'public, max-age=86400');
})->name('site.humans');

Route::get('/.well-known/security.txt', static function (): Response {
    $support = config('swaeduae.mail.support');
    $expires = now()->addYear()->endOfDay()->utc()->format('Y-m-d\TH:i:s\0\Z');
    $lines = [
        'Contact: mailto:'.$support,
        'Preferred-Languages: en, ar',
        'Expires: '.$expires,
    ];

    return response(implode("\n", $lines)."\n", 200)
        ->header('Content-Type', 'text/plain; charset=UTF-8')
        ->header('Cache-Control', 'public, max-age=86400');
})->name('site.security');

Route::get('/', HomeController::class)->name('home');

Route::get('/about', [InstitutionalPageController::class, 'show'])
    ->defaults('cms_slug', 'about')
    ->defaults('fallback_view', 'public.about')
    ->name('about');
Route::get('/leadership', LeadershipEntryController::class)->name('leadership');
Route::get('/programs', ProgramsIndexController::class)->name('programs.index');
Route::get('/youth-councils', YouthCouncilsController::class)->name('youth-councils');
Route::get('/events', [PublicEventController::class, 'index'])->name('events.index');
Route::get('/events/{event}/calendar.ics', [PublicEventController::class, 'ics'])->name('events.ics');
Route::get('/events/{event}', [PublicEventController::class, 'show'])->name('events.show');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/feed.xml', MediaAtomFeedController::class)->name('feed');
Route::get('/favicon.svg', static function (): Response {
    $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" role="img" aria-label="SwaedUAE">
  <rect width="32" height="32" rx="6" fill="#047857"/>
  <path fill="#ecfdf5" d="M8 22c4-6 6-10 6-14a3 3 0 0 1 6 0c0 4 2 8 6 14H8z"/>
</svg>
SVG;

    return response($svg, 200)
        ->header('Content-Type', 'image/svg+xml; charset=UTF-8')
        ->header('Cache-Control', 'public, max-age=86400');
})->name('site.favicon');

Route::get('/site.webmanifest', static function (): Response {
    $iconUrl = url('/favicon.svg');
    $manifest = [
        'name' => 'SwaedUAE',
        'short_name' => 'SwaedUAE',
        'description' => 'SwaedUAE youth volunteering and institutional website.',
        'start_url' => '/',
        'scope' => '/',
        'display' => 'browser',
        'background_color' => '#ffffff',
        'theme_color' => '#047857',
        'lang' => 'en',
        'icons' => [
            [
                'src' => $iconUrl,
                'sizes' => 'any',
                'type' => 'image/svg+xml',
                'purpose' => 'any',
            ],
        ],
    ];

    return response(
        json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
        200,
        ['Content-Type' => 'application/manifest+json; charset=UTF-8']
    );
})->name('site.webmanifest');
Route::get('/media', MediaHubController::class)->name('media.index');
Route::get('/media/external/{external_news_item}', [ExternalNewsPublicController::class, 'show'])
    ->name('media.external.show');
Route::get('/gallery', GalleryPublicController::class)->name('gallery');
Route::get('/partners', [InstitutionalPageController::class, 'show'])
    ->defaults('cms_slug', 'partners')
    ->defaults('fallback_view', 'public.partners')
    ->name('partners');
Route::get('/faq', [InstitutionalPageController::class, 'show'])
    ->defaults('cms_slug', 'faq')
    ->defaults('fallback_view', 'public.faq')
    ->name('faq');

Route::get('/page/{slug}', [CmsPageController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('cms.page');
Route::get('/legal/terms', [InstitutionalPageController::class, 'show'])
    ->defaults('cms_slug', 'terms')
    ->defaults('fallback_view', 'public.legal.terms')
    ->name('legal.terms');
Route::get('/legal/privacy', [InstitutionalPageController::class, 'show'])
    ->defaults('cms_slug', 'privacy')
    ->defaults('fallback_view', 'public.legal.privacy')
    ->name('legal.privacy');
Route::get('/legal/cookies', [InstitutionalPageController::class, 'show'])
    ->defaults('cms_slug', 'cookies')
    ->defaults('fallback_view', 'public.legal.cookies')
    ->name('legal.cookies');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:guest-contact-form')
    ->name('contact.store');

Route::get('/support', [SupportController::class, 'show'])->name('support.show');
Route::post('/support', [SupportController::class, 'store'])
    ->middleware('throttle:guest-support-form')
    ->name('support.store');

Route::get('/volunteer', [VolunteerHubController::class, 'index'])->name('volunteer.index');
Route::get('/feeds/volunteer-opportunities.atom', VolunteerOpportunitiesAtomFeedController::class)
    ->name('volunteer.opportunities.feed');
Route::get('/volunteer/opportunities', [VolunteerHubController::class, 'opportunities'])->name('volunteer.opportunities.index');
Route::get('/volunteer/opportunities/{event}/check-in', [VolunteerHubController::class, 'redirectToAttendanceCheckpoint'])
    ->middleware(['auth', 'verified', 'throttle:30,1'])
    ->name('volunteer.opportunities.attendance');
Route::get('/volunteer/opportunities/{event}', [VolunteerHubController::class, 'showOpportunity'])->name('volunteer.opportunities.show');
Route::post('/volunteer/opportunities/{event}/join', [VolunteerHubController::class, 'joinOpportunity'])
    ->middleware(['auth', 'verified', 'throttle:20,1'])
    ->name('volunteer.opportunities.join');
Route::post('/volunteer/opportunities/{event}/leave', [VolunteerHubController::class, 'leaveOpportunity'])
    ->middleware(['auth', 'verified', 'throttle:20,1'])
    ->name('volunteer.opportunities.leave');
Route::post('/volunteer/opportunities/{event}/apply', [VolunteerHubController::class, 'applyToOpportunity'])
    ->middleware(['auth', 'verified', 'throttle:20,1'])
    ->name('volunteer.opportunities.apply');
Route::post('/volunteer/opportunities/{event}/withdraw-application', [VolunteerHubController::class, 'withdrawApplication'])
    ->middleware(['auth', 'verified', 'throttle:20,1'])
    ->name('volunteer.opportunities.withdraw-application');
Route::post('/volunteer/opportunities/{event}/save', [VolunteerHubController::class, 'saveOpportunity'])
    ->middleware(['auth', 'verified', 'throttle:20,1'])
    ->name('volunteer.opportunities.save');
Route::delete('/volunteer/opportunities/{event}/save', [VolunteerHubController::class, 'unsaveOpportunity'])
    ->middleware(['auth', 'verified', 'throttle:20,1'])
    ->name('volunteer.opportunities.unsave');
