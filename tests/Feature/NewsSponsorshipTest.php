<?php

namespace Tests\Feature;

use App\Purchase;
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
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'the-acme-company']);

        $sponsorableSlots = new EloquentCollection([
            factory(SponsorableSlot::class)->create(['sponsorable_id' => $sponsorable->id]),
            factory(SponsorableSlot::class)->create(['sponsorable_id' => $sponsorable->id]),
            factory(SponsorableSlot::class)->create(['sponsorable_id' => $sponsorable->id]),
        ]);
        $response = $this->get('/the-acme-company/sponsorships/new');

        $response->assertSuccessful();
        $this->assertTrue($response->data('sponsorable')->is($sponsorable));
        $sponsorableSlots->assertEquals($response->data('sponsorableSlots'));
    }

    /** @test */
    public function sponsorable_slots_are_listed_in_chronological_order()
    {
        $sponsorable      = factory(Sponsorable::class)->create(['slug' => 'the-acme-company']);

        $slotA = factory(SponsorableSlot::class)
                                ->create([
                                    'publish_date'   => now(),
                                    'sponsorable_id' => $sponsorable->id,
                                ]);
        $slotB = factory(SponsorableSlot::class)
                                ->create([
                                    'publish_date'   => now()->addWeeks(1),
                                    'sponsorable_id' => $sponsorable->id,
                                ]);
        $slotC = factory(SponsorableSlot::class)
                                ->create([
                                    'publish_date'   => now()->addWeeks(2),
                                    'sponsorable_id' => $sponsorable->id,
                                ]);

        $response = $this->get('/the-acme-company/sponsorships/new');
        $response->assertSuccessful();
        $this->assertTrue($response->data('sponsorable')->is($sponsorable));

        $this->assertCount(3, $response->data('sponsorableSlots'));
        $this->assertTrue($response->data('sponsorableSlots')[0]->is($slotA));
        $this->assertTrue($response->data('sponsorableSlots')[1]->is($slotB));
        $this->assertTrue($response->data('sponsorableSlots')[2]->is($slotC));
    }

    /** @test */
    public function only_upcoming_sponsorable_slots_are_listed()
    {
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'the-acme-company']);
        $slotA       = factory(SponsorableSlot::class)
            ->create([
                'publish_date'   => now()->subDays(10),
                'sponsorable_id' => $sponsorable->id,
            ]);
        $slotB = factory(SponsorableSlot::class)
            ->create([
                'publish_date'   => now()->addDays(3),
                'sponsorable_id' => $sponsorable->id,
            ]);
        $slotC = factory(SponsorableSlot::class)
            ->create([
                'publish_date'   => now()->addDays(5),
                'sponsorable_id' => $sponsorable->id,
            ]);
        $slotD = factory(SponsorableSlot::class)
            ->create([
                'publish_date'   => now()->addDays(7),
                'sponsorable_id' => $sponsorable->id,
            ]);
        $response = $this->get('/the-acme-company/sponsorships/new');
        $response->assertSuccessful();
        $this->assertTrue($response->data('sponsorable')->is($sponsorable));

        $this->assertCount(3, $response->data('sponsorableSlots'));
        $this->assertTrue($response->data('sponsorableSlots')[0]->is($slotB));
        $this->assertTrue($response->data('sponsorableSlots')[1]->is($slotC));
        $this->assertTrue($response->data('sponsorableSlots')[2]->is($slotD));
    }

    /** @test */
    public function only_purchasable_sponsorable_slots_are_listed()
    {
        $sponsorable = factory(Sponsorable::class)->create(['slug' => 'the-acme-company']);
        $purchase    = factory(Purchase::class)->create();

        $slotA            = factory(SponsorableSlot::class)
                                ->create([
                                    'sponsorable_id' => $sponsorable->id,
                                ]);
        $slotB = factory(SponsorableSlot::class)
                                ->create([
                                    'sponsorable_id' => $sponsorable->id,
                                    'purchase_id'    => $purchase->id,
                                ]);
        $slotC = factory(SponsorableSlot::class)
                                ->create([
                                    'sponsorable_id' => $sponsorable->id,
                                    'purchase_id'    => $purchase->id,
                                ]);
        $slotD = factory(SponsorableSlot::class)
                                ->create([
                                    'sponsorable_id' => $sponsorable->id,
                                ]);
        $response    = $this->get('/the-acme-company/sponsorships/new');
        $response->assertSuccessful();
        $this->assertTrue($response->data('sponsorable')->is($sponsorable));

        $this->assertCount(2, $response->data('sponsorableSlots'));
        $this->assertTrue($response->data('sponsorableSlots')[0]->is($slotA));
        $this->assertTrue($response->data('sponsorableSlots')[1]->is($slotD));
    }
}
