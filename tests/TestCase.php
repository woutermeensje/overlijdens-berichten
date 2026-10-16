<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Geen echte geocoding-calls in tests.
        Http::preventStrayRequests();
        $this->fakeGeocoder();
    }

    protected function fakeGeocoder(): void
    {
        Http::fake(['api.pdok.nl/*' => Http::response(['response' => ['docs' => []]])]);
    }
}
