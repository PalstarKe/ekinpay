<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::connection('radius')->create('nas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->string('nasname');
            $table->string('shortname')->nullable();
            $table->string('secret');
            $table->boolean('nasapi')->default(0);
            $table->string('type')->default('other');
            $table->string('server')->default('radius');
            $table->string('community')->default('');
            $table->string('description')->default('Dynamically added NAS');
            $table->string('api_port')->default('8728');
            $table->timestamp('checkedTime')->nullable();
            $table->string('status')->default('offline');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('nas');
    }
};
