<?php

namespace Tests\Fakes;

use App\Exceptions\PaymentFailedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FakePaymentGateway;
use Tests\TestCase;

class FakePaymentsGatewayTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function retrieving_charges()
    {
        $paymentGateway =  new FakePaymentGateway();

        $paymentGateway->charge('john@email.com', 25000, $paymentGateway->validTestToken(), 'Example description A');
        $paymentGateway->charge('joe@email.com', 5000, $paymentGateway->validTestToken(), 'Example description B');
        $paymentGateway->charge('julia@email.com', 7500, $paymentGateway->validTestToken(), 'Example description C');

        $charges = $paymentGateway->charges();
        $this->assertCount(3, $charges);

        $this->assertEquals('john@email.com', $charges[0]->email());
        $this->assertEquals(25000, $charges[0]->amount());
        $this->assertEquals('Example description A', $charges[0]->description());

        $this->assertEquals('joe@email.com', $charges[1]->email());
        $this->assertEquals(5000, $charges[1]->amount());
        $this->assertEquals('Example description B', $charges[1]->description());

        $this->assertEquals('julia@email.com', $charges[2]->email());
        $this->assertEquals(7500, $charges[2]->amount());
        $this->assertEquals('Example description C', $charges[2]->description());
    }

    /** @test */
    public function charging_requires_a_valid_payment_token()
    {
        $paymentGateway =  new FakePaymentGateway();
        try {
            $paymentGateway->charge('john@email.com', 25000, 'invalid-payment-token', 'Example description.');
            $this->fail('The charge succeeded even though the payment token was invalid.');
        } catch (PaymentFailedException $e) {
            $charges = $paymentGateway->charges();
            $this->assertCount(0, $charges);
        }
    }
}
