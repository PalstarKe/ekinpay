<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('radius_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('package_id');
            $table->timestamps();
        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('radius_groups');
    }
};
