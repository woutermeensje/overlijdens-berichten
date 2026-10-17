<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use App\Models\User;
use App\Support\FormTimer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    /** Onderwerpen van het contactformulier; de sleutels komen ook terug in ?onderwerp= links. */
    public const SUBJECTS = [
        'vraag' => 'Algemene vraag',
        'wijzigen' => 'Bericht wijzigen of offline halen',
        'factuur' => 'Uitgelicht bericht of factuur',
        'uitvaartondernemer' => 'Uitvaartondernemer',
        'alerts' => 'Alerts afmelden',
    ];

    public function show()
    {
        return view('pages.contact', ['subjects' => self::SUBJECTS]);
    }

    public function send(Request $request)
    {
        $timer = FormTimer::check($request);
        if ($timer === 'bot') {
            return back();
        }
        if ($timer === 'expired') {
            return back()->withInput()->with('contact_error', 'Het formulier is verlopen. Probeer het opnieuw.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', Rule::in(array_keys(self::SUBJECTS))],
            'notice' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'name.required' => 'Vul je naam in.',
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'subject.required' => 'Kies een onderwerp.',
            'subject.in' => 'Kies een onderwerp.',
            'message.required' => 'Schrijf je vraag of bericht.',
            'message.min' => 'Je bericht is te kort.',
            'message.max' => 'Je bericht is te lang (maximaal 5000 tekens).',
        ]);

        $data['subject_label'] = self::SUBJECTS[$data['subject']];

        foreach (User::adminEmails() as $adminEmail) {
            Mail::to($adminEmail)->send(new ContactMessageMail($data));
        }

        return redirect(route('contact').'#contactformulier')
            ->with('contact_success', 'Bedankt voor je bericht! We nemen zo snel mogelijk contact met je op.');
    }
}
