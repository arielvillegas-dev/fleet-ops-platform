<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('plate', 24);
            $table->unsignedInteger('capacity')->default(40);
            $table->string('status', 24)->default('active');
            $table->timestamps();
        });

        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name', 120);
            $table->string('origin_name', 120);
            $table->string('destination_name', 120);
            $table->decimal('planned_distance_km', 8, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->string('operator_name', 100);
            $table->string('status', 24)->default('scheduled');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->decimal('actual_distance_km', 8, 3)->default(0);
            $table->timestamps();
        });

        Schema::create('telemetry_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->string('client_event_id', 64);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy_meters', 6, 2);
            $table->decimal('speed_kmh', 6, 2)->default(0);
            $table->decimal('heading', 5, 2)->default(0);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->unique(['trip_id', 'client_event_id']);
            $table->index(['trip_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetry_points');
        Schema::dropIfExists('trips');
        Schema::dropIfExists('routes');
        Schema::dropIfExists('vehicles');
    }
};
