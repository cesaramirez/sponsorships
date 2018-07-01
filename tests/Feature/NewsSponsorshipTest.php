<?php

namespace Tests\Feature;

use App\Sponsorable;
use App\SponsorableSlot;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsSponsorshipTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function viewing_the_sponsorship_page()
    {
        $sponsorable      = factory(Sponsorable::class)->create(['slug' => 'the-acme-company']);
        $sponsorableSlots = new EloquentCollection([
            factory(SponsorableSlot::class)->create(['sponsorable_id' => $sponsorable->id]),
            factory(SponsorableSlot::class)->create(['sponsorable_id' => $sponsorable->id]),
            factory(SponsorableSlot::class)->create(['sponsorable_id' => $sponsorable->id]),
        ]);
        $response    = $this->withoutExceptionHandling()->get('/the-acme-company/sponsorships/new');
        $response->assertSuccessful();
        $this->assertTrue($response->data('sponsorable')->is($sponsorable));

        $sponsorableSlots->assertEquals($response->data('sponsorableSlots'));
    }
}
