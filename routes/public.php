<?php

use App\Http\Controllers\Public\AcceptOrganizationInvitationController;
use App\Http\Controllers\Public\CmsPageController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\ExternalNewsPublicController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\InstitutionalPageController;
use App\Http\Controllers\Public\MediaHubController;
use App\Http\Controllers\Public\OrganizationRegistrationController;
use App\Http\Controllers\Public\ProgramsIndexController;
use App\Http\Controllers\Public\PublicEventController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\SupportController;
use App\Http\Controllers\Public\YouthCouncilsController;
use App\Http\Controllers\Volunteer\VolunteerHubController;
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
        '',
        'Sitemap: '.route('sitemap', [], true),
    ];

    return response(implode("\n", $lines), 200)
        ->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('robots');

Route::get('/', HomeController::class)->name('home');

Route::get('/about', [InstitutionalPageController::class, 'show'])
    ->defaults('cms_slug', 'about')
    ->defaults('fallback_view', 'public.about')
    ->name('about');
Route::get('/leadership', [InstitutionalPageController::class, 'show'])
    ->defaults('cms_slug', 'leadership')
    ->defaults('fallback_view', 'public.leadership')
    ->name('leadership');
Route::get('/programs', ProgramsIndexController::class)->name('programs.index');
Route::get('/youth-councils', YouthCouncilsController::class)->name('youth-councils');
Route::get('/events', [PublicEventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [PublicEventController::class, 'show'])->name('events.show');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/media', MediaHubController::class)->name('media.index');
Route::get('/media/external/{external_news_item}', [ExternalNewsPublicController::class, 'show'])
    ->name('media.external.show');
Route::get('/gallery', [InstitutionalPageController::class, 'show'])
    ->defaults('cms_slug', 'gallery')
    ->defaults('fallback_view', 'public.gallery')
    ->name('gallery');
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
