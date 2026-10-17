<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use App\Models\User;
use App\Support\FormTimer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jan de Vries',
            'email' => 'jan@example.com',
            'subject' => 'wijzigen',
            'notice' => 'Maria de Vries',
            'message' => 'Graag de geboortedatum aanpassen naar 1 maart 1940.',
        ], $overrides);
    }

    private function send(array $data)
    {
        $token = FormTimer::token();
        $this->travel(10)->seconds();

        return $this->post(route('contact.send'), $data + ['form_token' => $token]);
    }

    public function test_contact_page_has_form_and_preselects_subject_from_link(): void
    {
        $this->get(route('contact', ['onderwerp' => 'uitvaartondernemer']))
            ->assertOk()
            ->assertSee('id="contactformulier"', false)
            ->assertSee('<option value="uitvaartondernemer" selected', false)
            ->assertSee('data-contact-subject="wijzigen"', false);
    }

    public function test_message_is_mailed_to_admins_with_reply_to_sender(): void
    {
        config(['app.admin_email' => null]);
        User::factory()->create(['email' => 'admin@example.com', 'is_admin' => true]);

        $this->send($this->payload())
            ->assertRedirect(route('contact').'#contactformulier')
            ->assertSessionHas('contact_success');

        Mail::assertSent(ContactMessageMail::class, fn (ContactMessageMail $mail): bool => $mail->hasTo('admin@example.com')
            && $mail->hasReplyTo('jan@example.com')
            && $mail->hasSubject('Contactformulier: Bericht wijzigen of offline halen')
            && str_contains($mail->render(), 'Graag de geboortedatum aanpassen'));
    }

    public function test_required_fields_are_validated(): void
    {
        $this->send($this->payload(['name' => '', 'email' => 'geen-email', 'message' => 'kort', 'subject' => 'iets']))
            ->assertSessionHasErrors(['name', 'email', 'message', 'subject']);

        Mail::assertNothingSent();
    }

    public function test_bots_are_ignored(): void
    {
        $this->post(route('contact.send'), $this->payload())->assertSessionMissing('contact_success');

        $token = FormTimer::token();
        $this->post(route('contact.send'), $this->payload(['form_token' => $token]))->assertSessionMissing('contact_success');

        $this->send($this->payload(['website' => 'https://spam.example']))->assertSessionMissing('contact_success');

        Mail::assertNothingSent();
    }
}
