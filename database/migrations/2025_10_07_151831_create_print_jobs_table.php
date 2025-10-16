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
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('file_id');
            $table->unsignedBigInteger('printer_id');
            $table->string('status')->default('pending');
            $table->integer('priority')->default(1);
            $table->integer('copies')->default(1);
            $table->string('color_mode')->default('color');
            $table->string('paper_size')->default('A4');
            $table->boolean('duplex')->default(false);
            $table->string('quality')->default('normal');
            $table->string('pages_range')->nullable();
            $table->integer('total_pages')->nullable();
            $table->decimal('cost', 8, 2)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
