<?php

namespace Tests\Feature;

use App\Mail\AdminNewSubscriptionMail;
use App\Mail\SubscriptionConfirmationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegionSubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_receive_a_notification_for_new_subscriptions(): void
    {
        config(['app.admin_email' => null]);

        User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        Mail::fake();

        $response = $this->post(route('newsletter.subscribe'), [
            'email' => 'subscriber@example.com',
            'region' => 'Amsterdam',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('newsletter_success');

        Mail::assertSent(SubscriptionConfirmationMail::class, fn (SubscriptionConfirmationMail $mail): bool => $mail->hasTo('subscriber@example.com'));
        Mail::assertSent(AdminNewSubscriptionMail::class, fn (AdminNewSubscriptionMail $mail): bool => $mail->hasTo('admin@example.com'));
    }
}
