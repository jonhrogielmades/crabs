<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crab_species', function (Blueprint $table) {
            $table->string('reference_name')->nullable()->after('reference_image_path');
            $table->string('reference_url')->nullable()->after('reference_name');
            $table->string('image_credit')->nullable()->after('reference_url');
        });
    }

    public function down(): void
    {
        Schema::table('crab_species', function (Blueprint $table) {
            $table->dropColumn(['reference_name', 'reference_url', 'image_credit']);
        });
    }
};
