<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SponsorableSlot extends Model
{
    /*
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['sponsorship_id'];

    public function scopeSponsorable($query)
    {
        return $query->whereNull('sponsorship_id')
                     ->where('publish_date', '>=', now());
    }
}
