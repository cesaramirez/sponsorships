<?php

namespace Tests\Feature;

use App\PaymentGateway;
use App\Sponsorable;
use App\SponsorableSlot;
use App\Sponsorship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FakePaymentGateway;
use Tests\TestCase;

class SponsorableSponsorshipTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function purchasing_available_sponsorships_slots()
    {
        $paymentGateway = $this->app->instance(PaymentGateway::class, new FakePaymentGateway());
        $sponsorable    = factory(Sponsorable::class)->create(['slug' => 'the-acme-company', 'name' => 'The Acme Company']);

        $slotA = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable->id, 'publish_date' => now()->addMonths(1)]);
        $slotB = factory(SponsorableSlot::class)->create(['price' => 30000, 'sponsorable_id' => $sponsorable->id, 'publish_date' => now()->addMonths(2)]);
        $slotC = factory(SponsorableSlot::class)->create(['price' => 25000, 'sponsorable_id' => $sponsorable->id, 'publish_date' => now()->addMonths(3)]);

        $response = $this->postJson('/the-acme-company/sponsorships', [
            'email'                  => 'john@email.com',
            'company_name'           => 'Digital TechnoSoft Inc',
            'amount'                 => 75000,
            'sponsorable_slots'      => [
                $slotA->getKey(), $slotC->getKey(),
            ],
        ]);

        $response->assertStatus(201);
        $this->assertEquals(1, Sponsorship::count());
        $sponsorship = Sponsorship::first();

        $this->assertEquals('john@email.com', $sponsorship->email);
        $this->assertEquals('Digital TechnoSoft Inc', $sponsorship->company_name);
        $this->assertEquals(75000, $sponsorship->amount);

        $this->assertEquals($sponsorship->getKey(), $slotA->fresh()->sponsorship_id);
        $this->assertEquals($sponsorship->getKey(), $slotC->fresh()->sponsorship_id);

        $this->assertNull($slotB->fresh()->sponsorship_id);

        $this->assertCount(1, $paymentGateway->charges());
        $charge = $paymentGateway->charges()->first();
        $this->assertEquals('john@email.com', $charge->email());
        $this->assertEquals(75000, $charge->amount());
        $this->assertEquals("{$sponsorable->name} sponsorship", $charge->description());
    }
}
