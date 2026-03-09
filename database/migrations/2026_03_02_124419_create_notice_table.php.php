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
        Schema::create('notice', function (Blueprint $table) {
            $table->id();
            
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->unsignedBigInteger('created_by');
            
            $table->dateTime('expired_at')->nullable();
            
            $table->string('type')->nullable();
            
            // ENUM for status
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            $table->string('image')->nullable();
            
            // JSON column for storing multiple users
            $table->json('notice_users')->nullable();
            
            // ENUM for notice type
            $table->enum('notice_type', ['private', 'public'])->default('public');
            
            $table->timestamps();

            // Optional foreign key (uncomment if needed)
            // $table->foreign('created_by')
            //       ->references('id')
            //       ->on('users')
            //       ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notice');
    }
};