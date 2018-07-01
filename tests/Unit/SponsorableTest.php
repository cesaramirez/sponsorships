<?php

namespace Tests\Unit;

use App\Sponsorable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function finding_a_sponsorable_by_slug()
    {
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'the-acme-company']);

        $foundSponsorable = Sponsorable::findOrFailBySlug('the-acme-company');
        $this->assertTrue($foundSponsorable->is($sponsorable));
    }

    /** @test */
    public function an_exception_is_thrown_if_a_sponsorable_cannot_be_found_by_slug()
    {
        $this->expectException(ModelNotFoundException::class);
        Sponsorable::findOrFailBySlug('the-company-not-exist');
    }
}
