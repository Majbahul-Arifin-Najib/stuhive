<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pet_posts', function (Blueprint $table) {
            $table->foreignId('post_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('pet_name', 60)->nullable();
            $table->string('spotted_at', 120)->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_posts');
    }
};
