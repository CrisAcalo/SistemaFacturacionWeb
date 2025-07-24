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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Solo agregamos las columnas que no existen
            if (!Schema::hasColumn('personal_access_tokens', 'description')) {
                $table->string('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('personal_access_tokens', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('abilities');
            }
            if (!Schema::hasColumn('personal_access_tokens', 'metadata')) {
                $table->json('metadata')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('personal_access_tokens', 'created_by_role')) {
                $table->string('created_by_role')->nullable()->after('tokenable_id');
            }
            // expires_at ya existe en Sanctum, no la agregamos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Solo eliminamos las columnas que agregamos
            $table->dropColumn(['description', 'is_active', 'metadata', 'created_by_role']);
        });
    }
};
