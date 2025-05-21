<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$schema = Capsule::schema();

$schema->create('subscriptions', function ($table) {
    $table->id();
    $table->string('olx_url');
    $table->string('email');
    $table->decimal('last_price', 10, 2)->nullable();
    $table->timestamp('last_checked_at')->nullable();
    $table->boolean('is_active')->default(false);
    $table->string('confirmation_token')->nullable();
    $table->timestamp('email_confirmed_at')->nullable();
    $table->timestamps();
    
    // Add unique constraint to prevent duplicate subscriptions
    $table->unique(['olx_url', 'email']);
}); 