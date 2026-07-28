<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recognition_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recognition_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category')->index();
            $table->text('description');
            $table->string('status')->default('open')->index();
            $table->text('admin_response')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('model_versions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('version');
            $table->text('description')->nullable();
            $table->json('classes')->nullable();
            $table->decimal('confidence_threshold', 4, 3)->default(0.600);
            $table->json('evaluation_metrics')->nullable();
            $table->timestamp('deployed_at')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
            $table->unique(['name', 'version']);
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value')->nullable();
            $table->string('value_type')->default('string');
            $table->string('group')->default('general')->index();
            $table->boolean('is_public')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action')->index();
            $table->string('entity_type')->index();
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('model_versions');
        Schema::dropIfExists('recognition_feedback');
    }
};
