<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSponsorableSlotsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sponsorable_slots', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sponsorable_id');
            $table->unsignedInteger('purchase_id')->nullable();
            $table->dateTime('publish_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('sponsorable_slots');
    }
}
