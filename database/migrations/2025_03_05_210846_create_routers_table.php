<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('routers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nas_id');
            $table->unsignedBigInteger('created_by');
            $table->string('name');
            $table->string('ip_address');
            $table->string('location')->nullable();
            $table->string('type')->nullable();
            $table->string('secret')->nullable();
            $table->string('api_port')->nullable();
            $table->timestamp('checkedTime')->nullable();
            $table->string('status')->default('offline');
            $table->timestamps();

            $table->foreign('nas_id')->references('id')->on('nas')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('routers');
    }
};
