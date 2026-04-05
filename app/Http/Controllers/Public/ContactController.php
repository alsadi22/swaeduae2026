<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('public.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:40',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
        ]);

        $localeQ = PublicLocale::query();

        if (trim((string) $request->input('contact_trap', '')) !== '') {
            return redirect()->route('contact.show', $localeQ)->with('success', __('Thank you. We will get back to you soon.'));
        }

        Mail::to(config('swaeduae.mail.info'))->send(new ContactFormMail($validated));

        return redirect()->route('contact.show', $localeQ)->with('success', __('Thank you. We will get back to you soon.'));
    }
}
