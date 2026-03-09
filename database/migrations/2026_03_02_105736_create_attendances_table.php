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
    Schema::create('attendances', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')
              ->constrained()
              ->onDelete('cascade');

        $table->date('date');

        $table->time('in_time')->nullable();
        $table->time('out_time')->nullable();

        $table->time('shift_hours')->nullable();
        $table->time('work_hours')->nullable();
        $table->time('ot_hours')->nullable();

        $table->string('status')->default('A'); // P, A, L

        $table->text('remarks')->nullable();

        $table->timestamps();

        $table->unique(['user_id', 'date']); // VERY IMPORTANT
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
