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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('emp_code')->unique();
            $table->string('image')->nullable();
            $table->string('email')->unique();
            $table->string('designation')->nullable();
            $table->date('joining_date')->nullable(); // Added joining date
            $table->string('aadhaar_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->decimal('years_of_experience', 5, 2)->nullable();
            $table->text('training_experience')->nullable();
            $table->string('previous_company_name')->nullable();
            $table->string('previous_designation')->nullable();
            $table->decimal('previous_company_duration', 5, 2)->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'manager', 'team_leader', 'sales', 'employee'])->default('employee');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // Added deleted_at for soft deletes
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop self-referencing FK first
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });

        // Drop child tables first
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');

        // Finally drop users
        Schema::dropIfExists('users');
    }
};
