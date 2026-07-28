<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recognition_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crab_species_id')->nullable()->constrained('crab_species')->nullOnDelete();
            $table->string('scan_reference')->unique();
            $table->string('original_image_path');
            $table->string('annotated_image_path')->nullable();
            $table->string('predicted_class')->nullable()->index();
            $table->decimal('confidence', 6, 5)->nullable()->index();
            $table->string('confidence_level')->default('unknown')->index();
            $table->string('recognition_status')->default('pending')->index();
            $table->decimal('blur_score', 8, 4)->nullable();
            $table->decimal('brightness_score', 8, 4)->nullable();
            $table->json('quality_warnings')->nullable();
            $table->json('bounding_box')->nullable();
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->string('model_name')->nullable();
            $table->string('model_version')->nullable();
            $table->json('ai_response')->nullable();
            $table->text('failure_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recognition_records');
    }
};
