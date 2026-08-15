<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_posts', function (Blueprint $table) {
            $table->foreignId('post_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->string('course_code', 15)->index();
            $table->string('faculty_initial', 10)->index();
            $table->string('file_path');
            $table->string('original_filename');
            $table->unsignedInteger('file_size')->default(0);
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_posts');
    }
};
