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
        $this->get(route('contact.show'))->assertOk();
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
                && $mail->hasTo(config('swaeduae.mail.info'));
        });
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
