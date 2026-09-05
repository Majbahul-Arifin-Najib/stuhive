<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_posts', function (Blueprint $table) {
            $table->foreignId('post_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('course_code', 15)->index();
            $table->string('consultation_day', 15);
            $table->date('consultation_date')->index();
            $table->time('consultation_time');
            $table->string('room', 20)->nullable();
            $table->unsignedSmallInteger('capacity')->default(10);
            $table->timestamp('postponed_at')->nullable();
            $table->string('postpone_reason', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_posts');
    }
};
