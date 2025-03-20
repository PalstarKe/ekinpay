<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('router_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('router_id');
            $table->unsignedBigInteger('package_id');
            $table->timestamps();

            $table->foreign('router_id')->references('id')->on('routers')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('router_packages');
    }
};
