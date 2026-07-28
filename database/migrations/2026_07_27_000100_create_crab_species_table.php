<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crab_species', function (Blueprint $table) {
            $table->id();
            $table->string('common_name');
            $table->string('scientific_name')->unique();
            $table->string('local_name')->nullable();
            $table->string('family')->nullable();
            $table->string('classification')->nullable();
            $table->text('habitat')->nullable();
            $table->text('description')->nullable();
            $table->text('visual_characteristics')->nullable();
            $table->string('edible_status')->nullable();
            $table->text('caution_notes')->nullable();
            $table->string('reference_image_path')->nullable();
            $table->string('model_class_name')->nullable()->index();
            $table->unsignedInteger('model_class_id')->nullable()->index();
            $table->boolean('is_supported')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crab_species');
    }
};
