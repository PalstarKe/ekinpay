<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'customers', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->integer('customer_id');
            $table->string('fullname')->nullable();
            $table->string('username')->nullable();
            $table->string('account')->nullable();
            $table->string('email')->nullable();
            $table->string('expiry_extended')->nullable();
            $table->string('contact')->nullable();
            $table->string('avatar', 100)->default('');
            $table->integer('created_by')->default(0);
            $table->integer('is_active')->default(1);
            $table->integer('corporate')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('service')->nullable();
            $table->integer('auto_renewal')->default(1);
            $table->string('mac_address')->nullable();
            $table->integer('maclock')->default(1);
            $table->string('static_ip')->nullable();
            $table->string('sms_group')->nullable();
            $table->string('charges')->nullable();
            $table->text('package')->nullable();
            $table->string('apartment')->nullable();
            $table->string('location')->nullable();
            $table->string('housenumber')->nullable();
            $table->string('expiry')->nullable();
            $table->string('expiry_status')->nullable();
            // $table->string('shipping_zip')->nullable();
            // $table->text('shipping_address')->nullable();
            $table->string('lang')->default('en');
            $table->decimal('balance',15)->default('0.00');
            $table->rememberToken();
            $table->timestamps();
        }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
