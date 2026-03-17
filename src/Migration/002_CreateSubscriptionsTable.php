<?php

namespace App\Migration;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateSubscriptionsTable
{
    public static function up(): void
    {
        if (!Capsule::schema()->hasTable('subscriptions')) {
            Capsule::schema()->create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');  // external user ID from auth service
                $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
                $table->enum('status', ['active', 'expired', 'canceled'])->default('active');
                $table->timestamp('starts_at');
                $table->timestamp('ends_at');
                $table->timestamps();
            });
        }
    }
    
    public static function down(): void
    {
        Capsule::schema()->dropIfExists('subscriptions');
    }
}