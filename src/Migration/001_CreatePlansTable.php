<?php

namespace App\Migration;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreatePlansTable
{
    public static function up(): void
    {
        if (!Capsule::schema()->hasTable('plans')) {
            Capsule::schema()->create('plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('type', ['free', 'paid']);
                $table->decimal('price', 8, 2)->default(0);
                $table->integer('duration_days');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public static function down(): void
    {
        Capsule::schema()->dropIfExists('plans');
    }
}
