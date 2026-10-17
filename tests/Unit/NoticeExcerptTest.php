<?php

namespace Tests\Unit;

use App\Models\MemorialNotice;
use PHPUnit\Framework\TestCase;

class NoticeExcerptTest extends TestCase
{
    public function test_excerpt_is_at_most_twelve_words_of_the_message(): void
    {
        $notice = new MemorialNotice([
            'content' => '<p>Met veel verdriet delen wij het overlijden van onze lieve vader, opa en overgrootvader Gert.</p>',
        ]);

        $this->assertSame('Met veel verdriet delen wij het overlijden van onze lieve vader, opa…', $notice->listExcerpt());
    }

    public function test_placeholder_when_there_is_no_message(): void
    {
        $notice = new MemorialNotice(['type' => MemorialNotice::TYPE_ROUWADVERTENTIE, 'deceased_first_name' => 'Huub Vervloed', 'content' => '<p></p>']);

        $this->assertSame('Bekijk de rouwadvertentie van Huub Vervloed.', $notice->listExcerpt());
    }
}
