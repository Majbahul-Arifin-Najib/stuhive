<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_posts', function (Blueprint $table) {
            $table->foreignId('post_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('product_name', 120);
            $table->decimal('price', 10, 2);
            $table->string('condition', 20)->default('used');
            $table->string('contact_number', 20)->nullable();
            $table->boolean('is_sold')->default(false)->index();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_posts');
    }
};
