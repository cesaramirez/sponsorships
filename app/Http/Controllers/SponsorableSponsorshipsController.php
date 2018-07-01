<?php

namespace App\Http\Controllers;

use App\Sponsorable;

class SponsorableSponsorshipsController extends Controller
{
    public function new($slug)
    {
        $sponsorable      = Sponsorable::findOrFailBySlug($slug);
        $sponsorableSlots = $sponsorable->slots()
                                        ->where('publish_date', '>=', now())
                                        ->orderBy('publish_date')
                                        ->get();

        return view('sponsorable-sponsorships.new', compact('sponsorable', 'sponsorableSlots'));
    }
}
