<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('radius_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('nas_id');
            $table->string('username');
            $table->string('password');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('nas_id')->references('id')->on('radius.nas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('radius_users');
    }
};

