<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->string('name_plan');
            $table->decimal('price', 10, 2);
            $table->string('type');
            $table->string('typebp');
            $table->string('limit_type');
            $table->integer('time_limit')->nullable();
            $table->string('time_unit')->nullable();
            $table->integer('data_limit')->nullable();
            $table->string('data_unit')->nullable();
            $table->integer('validity');
            $table->string('validity_unit');
            $table->integer('shared_users');
            // $table->boolean('enabled')->default(true);
            $table->string('device')->nullable();
            $table->json('assigned_to')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('packages');
    }
};
