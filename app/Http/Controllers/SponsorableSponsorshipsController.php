<?php

namespace App\Http\Controllers;

use App\Sponsorable;
use App\SponsorableSlot;
use App\Sponsorship;

class SponsorableSponsorshipsController extends Controller
{
    public $paymentGateway;

    public function __construct($paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function new($slug)
    {
        $sponsorable      = Sponsorable::findOrFailBySlug($slug);
        $sponsorableSlots = $sponsorable->slots()
                                        ->sponsorable()
                                        ->orderBy('publish_date')
                                        ->get();

        return view('sponsorable-sponsorships.new', compact('sponsorable', 'sponsorableSlots'));
    }

    public function store($slug)
    {
        $sponsorable = Sponsorable::findOrFailBySlug($slug);
        $sponsorship = Sponsorship::create([
                            'email'        => request('email'),
                            'company_name' => request('company_name'),
                            'amount'       => request('amount'),
                        ]);

        $slots = SponsorableSlot::whereIn('id', request('sponsorable_slots'))->get();

        $this->paymentGateway->charge(request('email'), $slots->sum('price'), 'tok', "{$sponsorable->name} sponsorship");
        $slots->each->update(['sponsorship_id' => $sponsorship->id]);

        return response()->json([], 201);
    }
}
