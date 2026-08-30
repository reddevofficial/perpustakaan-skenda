<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number', 30)->unique();
            $table->foreignId('murid_id')->constrained('murid')->restrictOnDelete();
            $table->string('status', 20)->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('borrowed_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('loan_number');
            $table->index('status');
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
