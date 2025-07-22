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
        Schema::create('documents_manager', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('position')->default(0);
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('last_edited_by_id')->nullable();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
            $table->enum('state', ['draft', 'approved'])->default('draft');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('parent_id')->references('id')->on('documents_manager')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('last_edited_by_id')->references('id')->on('users')->onDelete('set null');
        });

        // Create document_manager_reviewer table
        Schema::create('document_manager_reviewer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_manager_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('document_manager_id')->references('id')->on('documents_manager')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_manager_reviewer');
        Schema::dropIfExists('documents_manager');
    }
}; 