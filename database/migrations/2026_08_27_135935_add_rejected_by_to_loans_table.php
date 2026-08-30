<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['rejected_by']);
            $table->dropColumn('rejected_by');
        });
    }
};
