<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Código único para que un estudiante invitado reclame su cuenta
            $table->string('claim_code', 10)->nullable()->unique()->after('role');
            // Indica si la cuenta es temporal (sin email/password reales)
            $table->boolean('is_guest')->default(false)->after('claim_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['claim_code', 'is_guest']);
        });
    }
};
