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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            // Employee who applied leave
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Manager of the employee
            $table->foreignId('manager_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Who approved or rejected the leave
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Leave Details
            |--------------------------------------------------------------------------
            */

            $table->string('leave_type'); // sick, casual, annual etc.
            $table->date('start_date');
            $table->date('end_date');

            $table->text('reason');
            $table->text('remarks')->nullable();

            // pending / approved / rejected
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};