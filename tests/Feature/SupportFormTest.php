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

    public function test_support_page_renders(): void
    {
        $this->get(route('support.show'))->assertOk();
    }

    public function test_support_form_action_includes_lang_when_page_requested_with_lang(): void
    {
        $storeUrl = route('support.store', ['lang' => 'ar'], false);

        $this->get(route('support.show', ['lang' => 'ar']))
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
            ->assertRedirect(route('support.show', ['lang' => 'ar']))
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
            ->assertRedirect(route('support.show', PublicLocale::query()))
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
            ->assertRedirect(route('support.show', PublicLocale::query()))
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
            $this->post(route('support.store'), $payload)->assertRedirect(route('support.show', PublicLocale::query()));
        }

        $this->post(route('support.store'), $payload)->assertStatus(429);
    }
}
