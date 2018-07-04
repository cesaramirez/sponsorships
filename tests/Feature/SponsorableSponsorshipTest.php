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
            'payment_token'          => $paymentGateway->validTestToken(),
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

    /** @test */
    public function sponsorship_is_not_created_if_payment_token_cannot_be_charged()
    {
        $paymentGateway = $this->app->instance(PaymentGateway::class, new FakePaymentGateway());
        $sponsorable    = factory(Sponsorable::class)->create(['slug' => 'the-acme-company', 'name' => 'The Acme Company']);

        $slot = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable->id, 'publish_date' => now()->addMonths(1)]);

        $response = $this->postJson('/the-acme-company/sponsorships', [
            'email'             => 'john@email.com',
            'company_name'      => 'Digital TechnoSoft Inc',
            'payment_token'     => 'not-at-valid-token',
            'sponsorable_slots' => [
                $slot->getKey(),
            ],
        ]);

        $response->assertStatus(422);

        $this->assertEquals(0, Sponsorship::count());
        $this->assertNull($slot->fresh()->sponsorship_id);
        $this->assertCount(0, $paymentGateway->charges());
    }

    /** @test */
    public function company_name_is_required()
    {
        $paymentGateway = $this->app->instance(PaymentGateway::class, new FakePaymentGateway());
        $sponsorable    = factory(Sponsorable::class)->create(['slug' => 'the-acme-company', 'name' => 'The Acme Company']);

        $slot = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable->id, 'publish_date' => now()->addMonths(1)]);

        $response = $this->withExceptionHandling()->postJson('/the-acme-company/sponsorships', [
            'email'             => 'john@email.com',
            'company_name'      => '',
            'payment_token'     => $paymentGateway->validTestToken(),
            'sponsorable_slots' => [
                $slot->getKey(),
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('company_name');

        $this->assertEquals(0, Sponsorship::count());
        $this->assertNull($slot->fresh()->sponsorship_id);
        $this->assertCount(0, $paymentGateway->charges());
    }

    /** @test */
    public function email_is_required()
    {
        $paymentGateway = $this->app->instance(PaymentGateway::class, new FakePaymentGateway());
        $sponsorable    = factory(Sponsorable::class)->create(['slug' => 'the-acme-company', 'name' => 'The Acme Company']);

        $slot = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable->id, 'publish_date' => now()->addMonths(1)]);

        $response = $this->withExceptionHandling()->postJson('/the-acme-company/sponsorships', [
            'email'             => '',
            'company_name'      => 'The Company Name',
            'payment_token'     => $paymentGateway->validTestToken(),
            'sponsorable_slots' => [
                $slot->getKey(),
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');

        $this->assertEquals(0, Sponsorship::count());
        $this->assertNull($slot->fresh()->sponsorship_id);
        $this->assertCount(0, $paymentGateway->charges());
    }

    /** @test */
    public function email_must_look_like_an_email()
    {
        $paymentGateway = $this->app->instance(PaymentGateway::class, new FakePaymentGateway());
        $sponsorable    = factory(Sponsorable::class)->create(['slug' => 'the-acme-company', 'name' => 'The Acme Company']);

        $slot = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable->id, 'publish_date' => now()->addMonths(1)]);

        $response = $this->withExceptionHandling()->postJson('/the-acme-company/sponsorships', [
            'email'             => 'not-valid-email',
            'company_name'      => 'The Company Name',
            'payment_token'     => $paymentGateway->validTestToken(),
            'sponsorable_slots' => [
                $slot->getKey(),
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');

        $this->assertEquals(0, Sponsorship::count());
        $this->assertNull($slot->fresh()->sponsorship_id);
        $this->assertCount(0, $paymentGateway->charges());
    }

    /** @test */
    public function payment_token_is_required()
    {
        $paymentGateway = $this->app->instance(PaymentGateway::class, new FakePaymentGateway());
        $sponsorable    = factory(Sponsorable::class)->create(['slug' => 'the-acme-company', 'name' => 'The Acme Company']);

        $slot = factory(SponsorableSlot::class)->create(['price' => 50000, 'sponsorable_id' => $sponsorable->id, 'publish_date' => now()->addMonths(1)]);

        $response = $this->withExceptionHandling()->postJson('/the-acme-company/sponsorships', [
            'email'             => 'john@email.com',
            'company_name'      => 'The Company Name',
            'payment_token'     => null,
            'sponsorable_slots' => [
                $slot->getKey(),
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payment_token');

        $this->assertEquals(0, Sponsorship::count());
        $this->assertNull($slot->fresh()->sponsorship_id);
        $this->assertCount(0, $paymentGateway->charges());
    }
}
