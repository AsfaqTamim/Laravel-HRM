<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_times', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['default', 'employee'])->default('default');
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('late_count')->default(0)->comment('Allowed late arrivals before action');
            $table->integer('max_break_minutes')->default(60)->comment('Maximum break in minutes');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_times');
    }
};
