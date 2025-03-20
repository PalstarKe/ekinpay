<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('smsdelivered', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->string('responseid')->nullable();
            $table->unsignedBigInteger('smsalert');
            $table->string('destination', 20);
            $table->string('message', 250);
            $table->dateTime('datetime');
            $table->unsignedBigInteger('adminid');
            $table->unsignedBigInteger('userid');
            $table->text('sms_api_response')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('smsdelivered');
    }
};

