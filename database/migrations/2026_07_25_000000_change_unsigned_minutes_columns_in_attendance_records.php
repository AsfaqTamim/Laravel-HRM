<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->smallInteger('late_minutes')->default(0)->change();
            $table->smallInteger('early_leave_minutes')->default(0)->change();
            $table->smallInteger('break_minutes')->default(0)->change();
            $table->smallInteger('overtime_minutes')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unsignedSmallInteger('late_minutes')->default(0)->change();
            $table->unsignedSmallInteger('early_leave_minutes')->default(0)->change();
            $table->unsignedSmallInteger('break_minutes')->default(0)->change();
            $table->unsignedSmallInteger('overtime_minutes')->default(0)->change();
        });
    }
};
