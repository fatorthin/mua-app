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
        Schema::create('pricelists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('theme_template')->default('rose_blush'); // rose_blush, luxury_gold, clean_nude, sage_botanical
            $table->string('primary_color')->nullable()->default('#ec4899');
            $table->string('cover_image_path')->nullable();
            $table->json('terms_conditions')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_social_media')->default(true);
            $table->boolean('show_contact_button')->default(true);
            $table->text('custom_footer_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pricelist_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricelist_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('pricelist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricelist_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->string('duration_text')->nullable();
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_highlighted')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricelist_items');
        Schema::dropIfExists('pricelist_sections');
        Schema::dropIfExists('pricelists');
    }
};
