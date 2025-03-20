<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('smsalerts', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('created_by');
            $table->string('type', 50);
            $table->tinyInteger('status')->default(1);
            $table->text('template')->nullable();
            $table->timestamps();
        });

        // Insert default data
        DB::table('smsalerts')->insert([
            ['id' => 1, 'type' => 'Admin Panel Login', 'status' => 1, 'template' => 'Dear Admin {username}, a new login attempt into your account at {datetime} on {company}.'],
            ['id' => 2, 'type' => 'User Panel Login', 'status' => 1, 'template' => 'Dear User {username}, a new login attempt into your account at {datetime} on {company}.'],
            ['id' => 3, 'type' => 'Portal & Connection Login Info Changed', 'status' => 1, 'template' => 'Dear User {username}, your internet connection information has been changed to username: {username} and password: {password}.'],
            ['id' => 4, 'type' => 'Connection Info Changed (Off)', 'status' => 0, 'template' => null],
            ['id' => 5, 'type' => 'New Admin (Off)', 'status' => 0, 'template' => null],
            ['id' => 6, 'type' => 'New Staff (Off)', 'status' => 0, 'template' => null],
            ['id' => 7, 'type' => 'New Reseller (Off)', 'status' => 0, 'template' => null],
            ['id' => 8, 'type' => 'New User', 'status' => 1, 'template' => 'Dear User {username}, Thank you for choosing {company}. Your username: {username} and password: {password}.'],
            ['id' => 10, 'type' => 'Add Reseller Balance', 'status' => 1, 'template' => 'Dear User {username}, {currency}{amount} has been deposited into your account.'],
            ['id' => 11, 'type' => 'Add User Balance', 'status' => 1, 'template' => 'Dear User {username}, {currency}{amount} has been deposited into your account.'],
            ['id' => 12, 'type' => 'User Profile Enable/Disable', 'status' => 1, 'template' => 'Dear User {username}, your account status has been changed to {status}.'],
            ['id' => 16, 'type' => 'User Expired', 'status' => 1, 'template' => 'Dear User {username}, your internet connection has been terminated on {expirytime}. Please, pay your subscription fee for activation.'],
            ['id' => 17, 'type' => 'User Activated', 'status' => 1, 'template' => 'Dear User {username}, your internet connection has been renewed and new validity till {expirytime}.'],
            ['id' => 18, 'type' => 'User Expiry Notice Before 3 Days', 'status' => 1, 'template' => 'Dear User {username}, your internet connection will be terminated on {expirytime}. Please, pay your subscription fee before termination.'],
            ['id' => 19, 'type' => 'User Expiry Notice Before 1 Day', 'status' => 1, 'template' => 'Dear User {username}, your internet connection will be terminated on {expirytime}. Please, pay your subscription fee before termination.'],
            ['id' => 20, 'type' => 'Login - 2FA By OTP', 'status' => 1, 'template' => '{otp} is your OTP Code For Login From {company}'],
            ['id' => 21, 'type' => 'Register - 2FA By OTP', 'status' => 0, 'template' => '{otp} is your OTP Code For Registration From {company}'],
            ['id' => 22, 'type' => 'Password Reset - 2FA By OTP', 'status' => 0, 'template' => '{otp} is your OTP Code For Password Reset From {company}'],
            ['id' => 23, 'type' => 'Mobile Number Reset - 2FA By OTP', 'status' => 0, 'template' => '{otp} is your OTP Code For Mobile Number Reset From {company}'],
            ['id' => 24, 'type' => 'Ticket SMS Notification', 'status' => 0, 'template' => 'A New Ticket Has Been Created {title} {company}'],
            ['id' => 25, 'type' => 'Notice SMS Notification', 'status' => 0, 'template' => 'A New Notice Has Been Created {title} {company}'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smsalerts');
    }
};
