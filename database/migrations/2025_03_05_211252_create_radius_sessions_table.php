<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('radius_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('radius_user_id'); // Links to radius_users
            $table->string('session_id');
            $table->string('ip_address');
            $table->string('status'); // Active, Expired, etc.
            $table->timestamps();

            $table->foreign('radius_user_id')->references('id')->on('radius_users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('radius_sessions');
    }
};

