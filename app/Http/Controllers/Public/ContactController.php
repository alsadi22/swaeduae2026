<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactController extends Controller
{
    private const CONTACT_TYPES = ['general', 'partnership', 'media', 'youth_programmes', 'data_rights'];

    /** @var list<string> */
    private const SUPPORT_TOPICS = ['login', 'registration', 'attendance', 'organization', 'certificate', 'other'];

    public function show(Request $request): View
    {
        $raw = $request->query('contact_type');
        $prefill = is_string($raw) && in_array($raw, self::CONTACT_TYPES, true) ? $raw : null;

        $topicRaw = $request->query('topic');
        $supportPrefill = is_string($topicRaw) && in_array($topicRaw, self::SUPPORT_TOPICS, true) ? $topicRaw : null;

        return view('public.contact', [
            'contactTypePrefill' => $prefill,
            'supportTopicPrefill' => $supportPrefill,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:40',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
            'contact_type' => ['nullable', 'string', Rule::in(['general', 'partnership', 'media', 'youth_programmes', 'data_rights'])],
        ]);

        $localeQ = PublicLocale::queryFromRequestOrUser($request->user());

        if (trim((string) $request->input('contact_trap', '')) !== '') {
            return redirect()->route('contact.show', $localeQ)->with('success', __('Thank you. We will get back to you soon.'));
        }

        $type = $validated['contact_type'] ?? 'general';
        $typeLabels = [
            'general' => __('Contact type general'),
            'partnership' => __('Contact type partnership'),
            'media' => __('Contact type media'),
            'youth_programmes' => __('Contact type youth programmes'),
            'data_rights' => __('Contact type data rights'),
        ];
        $payload = array_merge($validated, [
            'contact_type' => $type,
            'contact_type_label' => $typeLabels[$type],
        ]);

        $inbox = match ($type) {
            'youth_programmes' => config('swaeduae.mail.youth_councils'),
            'data_rights' => config('swaeduae.mail.privacy'),
            default => config('swaeduae.mail.info'),
        };

        Mail::to($inbox)->send(new ContactFormMail($payload));

        return redirect()->route('contact.show', $localeQ)->with('success', __('Thank you. We will get back to you soon.'));
    }
}
