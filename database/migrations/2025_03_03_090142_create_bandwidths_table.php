<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bandwidths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('created_by');
            $table->string('name_plan');
            $table->integer('rate_down');
            $table->string('rate_down_unit');
            $table->integer('rate_up');
            $table->string('rate_up_unit');
            $table->integer('burst')->nullable();
            $table->timestamps();

            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bandwidths');
    }
};

