<?php

namespace Tests\Feature;

use App\Mail\SupportFormMail;
use App\Support\PublicLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SupportFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_url_redirects_to_contact_page(): void
    {
        $this->get(route('support.show'))
            ->assertRedirect(route('contact.show', PublicLocale::mergeQuery([]), false));
    }

    public function test_support_url_with_topic_redirects_to_contact_with_topic(): void
    {
        $this->get(route('support.show', ['topic' => 'attendance']))
            ->assertRedirect(route('contact.show', PublicLocale::mergeQuery(['topic' => 'attendance']), false));
    }

    public function test_contact_page_includes_support_form_fields(): void
    {
        $this->get(route('contact.show'))
            ->assertOk()
            ->assertSee('data-testid="support-topic-select"', false)
            ->assertSee('autocomplete="name"', false)
            ->assertSee('autocomplete="email"', false)
            ->assertSee('autocomplete="tel"', false);
    }

    public function test_support_success_message_uses_accessible_live_region(): void
    {
        Mail::fake();

        $this->followingRedirects()
            ->post(route('support.store'), [
                'name' => 'Support User',
                'email' => 'support-live@example.com',
                'phone' => '',
                'subject' => 'Need help',
                'message' => str_repeat('s', 40),
                'topic' => 'other',
            ])
            ->assertOk()
            ->assertSee('data-testid="contact-form-success"', false)
            ->assertSee('aria-live="polite"', false);
    }

    public function test_support_topic_query_prefills_topic_select_via_redirect(): void
    {
        $this->get(route('support.show', ['topic' => 'attendance']))
            ->assertRedirect(route('contact.show', PublicLocale::mergeQuery(['topic' => 'attendance']), false));

        $this->get(route('contact.show', PublicLocale::mergeQuery(['topic' => 'attendance'])))
            ->assertOk()
            ->assertSee('data-testid="support-topic-select"', false)
            ->assertSee('<option value="attendance" selected', false);
    }

    public function test_support_invalid_topic_query_redirects_without_topic_param(): void
    {
        $this->get(route('support.show', ['topic' => 'invalid-topic-here']))
            ->assertRedirect(route('contact.show', PublicLocale::mergeQuery([]), false));

        $this->get(route('contact.show'))
            ->assertOk()
            ->assertSee('<option value="other" selected', false);
    }

    public function test_support_opportunities_footer_via_contact_page_with_locale(): void
    {
        $oppAr = route('volunteer.opportunities.index', ['lang' => 'ar'], false);

        $this->get(route('contact.show', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee('data-testid="contact-footer-opportunities"', false)
            ->assertSee($oppAr, false);
    }

    public function test_support_form_action_includes_lang_when_page_requested_with_lang(): void
    {
        $storeUrl = route('support.store', ['lang' => 'ar'], false);

        $this->get(route('contact.show', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee($storeUrl, false);
    }

    public function test_support_submission_redirect_preserves_lang_query(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Ar Help',
            'email' => 'ar-help@example.com',
            'phone' => '',
            'topic' => 'login',
            'subject' => 'Hi',
            'message' => str_repeat('m', 40),
        ];

        $this->post(route('support.store', ['lang' => 'ar']), $payload)
            ->assertRedirect(route('contact.show', ['lang' => 'ar']))
            ->assertSessionHas('success');

        Mail::assertSent(SupportFormMail::class);
    }

    public function test_support_form_sends_mail_to_support_inbox(): void
    {
        Mail::fake();

        $this->post(route('support.store'), [
            'name' => 'Volunteer Help',
            'email' => 'help@example.com',
            'phone' => '',
            'topic' => 'attendance',
            'subject' => 'Cannot check in',
            'message' => 'GPS issue at the gate.',
        ])
            ->assertRedirect(route('contact.show', PublicLocale::query()))
            ->assertSessionHas('success');

        Mail::assertSent(SupportFormMail::class, function (SupportFormMail $mail) {
            return $mail->payload['email'] === 'help@example.com'
                && $mail->payload['topic'] === 'attendance'
                && $mail->hasTo(config('swaeduae.mail.support'));
        });
    }

    public function test_support_form_rejects_invalid_topic(): void
    {
        Mail::fake();

        $this->post(route('support.store'), [
            'name' => 'User',
            'email' => 'user@example.com',
            'phone' => '',
            'topic' => 'not-a-valid-topic',
            'subject' => 'Hello',
            'message' => str_repeat('x', 40),
        ])
            ->assertSessionHasErrors('topic');

        Mail::assertNothingSent();
    }

    public function test_support_honeypot_filled_sends_no_mail_but_shows_success(): void
    {
        Mail::fake();

        $this->post(route('support.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'phone' => '',
            'topic' => 'other',
            'subject' => 'Spam',
            'message' => str_repeat('x', 50),
            'support_trap' => 'filled',
        ])
            ->assertRedirect(route('contact.show', PublicLocale::query()))
            ->assertSessionHas('success');

        Mail::assertNothingSent();
    }

    public function test_support_form_uses_named_rate_limiter(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Throttled User',
            'email' => 'throttle-support@example.com',
            'phone' => '',
            'topic' => 'login',
            'subject' => 'Hi',
            'message' => str_repeat('m', 40),
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('support.store'), $payload)->assertRedirect(route('contact.show', PublicLocale::query()));
        }

        $this->post(route('support.store'), $payload)->assertStatus(429);
    }
}
