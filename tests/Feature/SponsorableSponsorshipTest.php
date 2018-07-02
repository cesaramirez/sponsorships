<?php

namespace Tests\Feature;

use App\Sponsorable;
use App\SponsorableSlot;
use App\Sponsorship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorableSponsorshipTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function purchasing_available_sponsorships_slots()
    {
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'the-acme-company']);

        $slotA = factory(SponsorableSlot::class)->create(['sponsorable_id' => $sponsorable->id, 'publish_date' => now()->addMonths(1)]);
        $slotB = factory(SponsorableSlot::class)->create(['sponsorable_id' => $sponsorable->id, 'publish_date' => now()->addMonths(2)]);
        $slotC = factory(SponsorableSlot::class)->create(['sponsorable_id' => $sponsorable->id, 'publish_date' => now()->addMonths(3)]);

        $response = $this->postJson('/the-acme-company/sponsorships', [
            'sponsorable_slots' => [
                $slotA->getKey(), $slotC->getKey(),
            ],
        ]);

        $response->assertStatus(201);
        $this->assertEquals(1, Sponsorship::count());
        $sponsorship = Sponsorship::first();

        $this->assertEquals($sponsorship->getKey(), $slotA->fresh()->sponsorship_id);
        $this->assertEquals($sponsorship->getKey(), $slotC->fresh()->sponsorship_id);

        $this->assertNull($slotB->fresh()->sponsorship_id);
    }
}
