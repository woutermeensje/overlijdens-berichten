<?php

namespace Tests\Feature;

use App\Models\MemorialNotice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemorialNoticeCondolenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitors_can_leave_a_condolence_on_a_notice(): void
    {
        $notice = $this->createNotice();

        $response = $this->post(route('notice.condolences.store', $notice->slug), [
            'first_name' => 'Piet',
            'last_name' => 'Jansen',
            'email' => 'piet@example.com',
            'message' => 'Heel veel sterkte gewenst.',
        ]);

        $response->assertRedirect(route('notice.show', $notice->slug).'#condoleances');

        $this->assertDatabaseHas('memorial_notice_condolences', [
            'memorial_notice_id' => $notice->id,
            'first_name' => 'Piet',
            'last_name' => 'Jansen',
            'email' => 'piet@example.com',
            'message' => 'Heel veel sterkte gewenst.',
        ]);

        $this->get(route('notice.show', $notice->slug))
            ->assertOk()
            ->assertSee('Piet Jansen')
            ->assertSee('Heel veel sterkte gewenst.')
            ->assertDontSee('piet@example.com');
    }

    public function test_notice_page_shows_funeral_information_and_condolence_register_link(): void
    {
        $notice = $this->createNotice([
            'funeral_company_name' => 'Uitvaartzorg De Haven',
            'funeral_company_contact' => 'Anne de Boer',
            'funeral_company_phone' => '010-1234567',
            'funeral_company_email' => 'info@dehaven.test',
            'funeral_company_url' => 'https://dehaven.test',
            'condolence_url' => 'https://register.test/willem',
        ]);

        $this->get(route('notice.show', $notice->slug))
            ->assertOk()
            ->assertSee('Informatie over de uitvaart')
            ->assertSee('Uitvaartzorg De Haven')
            ->assertSee('Anne de Boer')
            ->assertSee('010-1234567')
            ->assertSee('Naar condoleanceregister')
            ->assertSee('https://register.test/willem');
    }

    private function createNotice(array $attributes = []): MemorialNotice
    {
        $user = User::factory()->create();

        return MemorialNotice::query()->create(array_merge([
            'user_id' => $user->id,
            'title' => 'Willem Johannes van der Starre',
            'slug' => 'willem-johannes-van-der-starre',
            'type' => MemorialNotice::TYPE_OVERLIJDENSBERICHT,
            'deceased_first_name' => 'Willem Johannes',
            'deceased_last_name' => 'van der Starre',
            'content' => '<p>Een liefdevol overlijdensbericht.</p>',
            'city' => 'Rotterdam',
            'province' => 'Zuid-Holland',
            'status' => 'published',
            'published_at' => now(),
        ], $attributes));
    }
}
