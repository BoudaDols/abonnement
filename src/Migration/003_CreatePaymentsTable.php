<?php

namespace App\Migration;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreatePaymentsTable
{
    public static function up(): void
    {
        if (!Capsule::schema()->hasTable('payments')) {
            Capsule::schema()->create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 8, 2);
                $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
                $table->string('transaction_id')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }
    }
    
    public static function down(): void
    {
        Capsule::schema()->dropIfExists('payments');
    }
}