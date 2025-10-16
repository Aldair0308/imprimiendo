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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('file_path');
            $table->bigInteger('file_size');
            $table->string('file_type');
            $table->string('mime_type');
            $table->integer('pages')->nullable();
            $table->unsignedBigInteger('session_id');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('uploaded');
            $table->string('checksum')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
