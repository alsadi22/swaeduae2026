<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use App\Support\PublicLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_renders(): void
    {
        $this->get(route('contact.show'))
            ->assertOk()
            ->assertSee('data-testid="contact-copy-page-url"', false)
            ->assertSee('data-testid="contact-type-select"', false)
            ->assertSee('data-testid="support-topic-select"', false)
            ->assertSee('autocomplete="name"', false)
            ->assertSee('autocomplete="email"', false)
            ->assertSee('autocomplete="tel"', false);
    }

    public function test_contact_success_message_uses_accessible_live_region(): void
    {
        Mail::fake();

        $this->followingRedirects()
            ->post(route('contact.store'), [
                'name' => 'Live Region',
                'email' => 'live@example.com',
                'phone' => '',
                'subject' => 'Hello',
                'message' => str_repeat('m', 40),
            ])
            ->assertOk()
            ->assertSee('data-testid="contact-form-success"', false)
            ->assertSee('aria-live="polite"', false);
    }

    public function test_contact_page_prefills_type_from_valid_query(): void
    {
        $this->get(route('contact.show', ['contact_type' => 'media']))
            ->assertOk()
            ->assertSee('data-testid="contact-type-select"', false)
            ->assertSee('<option value="media" selected', false);
    }

    public function test_contact_page_ignores_invalid_contact_type_query(): void
    {
        $this->get(route('contact.show', ['contact_type' => 'not-a-real-type']))
            ->assertOk()
            ->assertSee('<option value="general" selected', false);
    }

    public function test_contact_page_includes_opportunities_footer_with_locale(): void
    {
        $oppAr = route('volunteer.opportunities.index', ['lang' => 'ar'], false);

        $this->get(route('contact.show', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee('data-testid="contact-footer-opportunities"', false)
            ->assertSee($oppAr, false);
    }

    public function test_contact_form_action_includes_lang_when_page_requested_with_lang(): void
    {
        $storeUrl = route('contact.store', ['lang' => 'ar'], false);

        $this->get(route('contact.show', ['lang' => 'ar']))
            ->assertOk()
            ->assertSee($storeUrl, false);
    }

    public function test_contact_submission_redirect_preserves_lang_query(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Ar Sender',
            'email' => 'ar-sender@example.com',
            'phone' => '',
            'subject' => 'Hello',
            'message' => str_repeat('a', 40),
        ];

        $this->post(route('contact.store', ['lang' => 'ar']), $payload)
            ->assertRedirect(route('contact.show', ['lang' => 'ar']))
            ->assertSessionHas('success');

        Mail::assertSent(ContactFormMail::class);
    }

    public function test_contact_form_sends_mail_to_info_inbox(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name' => 'Test Sender',
            'email' => 'sender@example.com',
            'phone' => '',
            'subject' => 'Hello',
            'message' => 'This is a test message body.',
        ])
            ->assertRedirect(route('contact.show', PublicLocale::query()))
            ->assertSessionHas('success');

        Mail::assertSent(ContactFormMail::class, function (ContactFormMail $mail) {
            return $mail->payload['email'] === 'sender@example.com'
                && ($mail->payload['contact_type'] ?? 'general') === 'general'
                && $mail->hasTo(config('swaeduae.mail.info'));
        });
    }

    public function test_contact_youth_programmes_type_routes_to_youth_inbox(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name' => 'Youth Sender',
            'email' => 'youth@example.com',
            'phone' => '',
            'subject' => 'Youth question',
            'message' => str_repeat('y', 40),
            'contact_type' => 'youth_programmes',
        ])
            ->assertRedirect(route('contact.show', PublicLocale::query()))
            ->assertSessionHas('success');

        Mail::assertSent(ContactFormMail::class, function (ContactFormMail $mail) {
            return $mail->payload['contact_type'] === 'youth_programmes'
                && $mail->hasTo(config('swaeduae.mail.youth_councils'));
        });
    }

    public function test_contact_data_rights_type_routes_to_privacy_inbox(): void
    {
        Mail::fake();
        config(['swaeduae.mail.privacy' => 'privacy-inbox@example.com']);

        $this->post(route('contact.store'), [
            'name' => 'Privacy Sender',
            'email' => 'privacy@example.com',
            'phone' => '',
            'subject' => 'Data request',
            'message' => str_repeat('p', 40),
            'contact_type' => 'data_rights',
        ])
            ->assertRedirect(route('contact.show', PublicLocale::query()))
            ->assertSessionHas('success');

        Mail::assertSent(ContactFormMail::class, function (ContactFormMail $mail) {
            return $mail->payload['contact_type'] === 'data_rights'
                && $mail->hasTo('privacy-inbox@example.com');
        });
    }

    public function test_contact_form_rejects_invalid_inquiry_type(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name' => 'Bad Type',
            'email' => 'bad@example.com',
            'phone' => '',
            'subject' => 'Hi',
            'message' => str_repeat('z', 40),
            'contact_type' => 'not-a-real-type',
        ])->assertSessionHasErrors('contact_type');

        Mail::assertNothingSent();
    }

    public function test_contact_honeypot_filled_sends_no_mail_but_shows_success(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'phone' => '',
            'subject' => 'Spam',
            'message' => str_repeat('x', 50),
            'contact_trap' => 'https://evil.example',
        ])
            ->assertRedirect(route('contact.show', PublicLocale::query()))
            ->assertSessionHas('success');

        Mail::assertNothingSent();
    }

    public function test_contact_form_uses_named_rate_limiter(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Throttled User',
            'email' => 'throttle@example.com',
            'phone' => '',
            'subject' => 'Hi',
            'message' => str_repeat('m', 40),
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.store'), $payload)->assertRedirect(route('contact.show', PublicLocale::query()));
        }

        $this->post(route('contact.store'), $payload)->assertStatus(429);
    }
}
