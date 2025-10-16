<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model')->nullable();
            $table->string('ip_address');
            $table->integer('port')->default(9100);
            $table->string('status')->default('offline');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(false);
            $table->json('capabilities')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->integer('max_queue_size')->default(10);
            $table->integer('current_queue_size')->default(0);
            $table->integer('total_jobs_printed')->default(0);
            $table->timestamp('last_health_check')->nullable();
            $table->timestamp('last_job_completed')->nullable();
            $table->integer('error_count')->default(0);
            $table->boolean('maintenance_mode')->default(false);
            $table->timestamp('last_maintenance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
