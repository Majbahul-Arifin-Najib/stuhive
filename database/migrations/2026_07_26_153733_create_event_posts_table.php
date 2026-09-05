<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_posts', function (Blueprint $table) {
            $table->foreignId('post_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('event_name', 200);
            $table->string('club_name', 120)->nullable();
            $table->string('venue', 120)->nullable();
            $table->date('event_date')->index();
            $table->time('event_time');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_posts');
    }
};
