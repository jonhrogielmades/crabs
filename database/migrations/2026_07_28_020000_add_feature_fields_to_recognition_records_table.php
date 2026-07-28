<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('recognition_records', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('failure_reason')->index();
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude')->index();
            $table->string('location_label')->nullable()->after('longitude');
            $table->text('capture_notes')->nullable()->after('location_label');
            $table->foreignId('expert_species_id')->nullable()->after('capture_notes')->constrained('crab_species')->nullOnDelete();
            $table->boolean('needs_retraining')->default(false)->after('expert_species_id')->index();
            $table->text('admin_notes')->nullable()->after('needs_retraining');
        });
    }

    public function down(): void
    {
        Schema::table('recognition_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expert_species_id');
            $table->dropColumn(['latitude', 'longitude', 'location_label', 'capture_notes', 'needs_retraining', 'admin_notes']);
        });
    }
};
