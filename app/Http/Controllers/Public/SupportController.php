<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\SupportFormMail;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    private const SUPPORT_TOPICS = ['login', 'registration', 'attendance', 'organization', 'certificate', 'other'];

    public function show(Request $request): RedirectResponse
    {
        $raw = $request->query('topic');
        $prefill = is_string($raw) && in_array($raw, self::SUPPORT_TOPICS, true) ? $raw : null;

        $q = PublicLocale::queryFromRequestOrUser($request->user());
        if ($prefill !== null) {
            $q['topic'] = $prefill;
        }

        return redirect()->route('contact.show', $q, 301);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:40',
            'topic' => 'required|string|in:login,registration,attendance,organization,certificate,other',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
        ]);

        $localeQ = PublicLocale::queryFromRequestOrUser($request->user());

        if (trim((string) $request->input('support_trap', '')) !== '') {
            return redirect()->route('contact.show', $localeQ)->with('success', __('Thank you. We will get back to you soon.'));
        }

        $validated['topic_label'] = match ($validated['topic']) {
            'login' => __('Support topic login'),
            'registration' => __('Support topic registration'),
            'attendance' => __('Support topic attendance'),
            'organization' => __('Support topic organization account'),
            'certificate' => __('Support topic certificate'),
            default => __('Support topic other'),
        };

        Mail::to(config('swaeduae.mail.support'))->send(new SupportFormMail($validated));

        return redirect()->route('contact.show', $localeQ)->with('success', __('Thank you. We will get back to you soon.'));
    }
}
