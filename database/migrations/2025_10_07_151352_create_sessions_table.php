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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_code')->unique();
            $table->string('status')->default('active');
            $table->string('client_ip')->nullable();
            $table->timestamp('expires_at');
            $table->integer('max_files')->default(10);
            $table->bigInteger('max_total_size')->default(52428800); // 50MB
            $table->boolean('qr_generated')->default(false);
            $table->string('qr_code_path')->nullable();
            $table->string('qr_code_url')->nullable();
            $table->timestamp('qr_generated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
