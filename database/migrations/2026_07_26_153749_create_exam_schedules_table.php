<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('course_code', 15)->index();
            $table->string('section', 5);
            $table->string('day', 15);
            $table->date('exam_date')->index();
            $table->time('exam_time');
            $table->string('room_number', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
