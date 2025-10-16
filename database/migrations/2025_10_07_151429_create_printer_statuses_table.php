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
        Schema::create('printer_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('printer_id')->constrained()->onDelete('cascade');
            $table->boolean('is_online')->default(false);
            $table->integer('paper_level')->default(0);
            $table->integer('ink_level')->default(0);
            $table->integer('temperature')->default(0);
            $table->timestamp('last_check')->nullable();
            $table->integer('error_count')->default(0);
            $table->string('status_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_statuses');
    }
};
