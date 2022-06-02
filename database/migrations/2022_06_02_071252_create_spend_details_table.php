<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpendDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spend_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spend_id');
            $table->string('day',2);
            $table->float('total');
            $table->text('description');
            $table->timestamps();
            
            $table->foreign('spend_id')->references('id')->on('spends')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spend_details');
    }
}
