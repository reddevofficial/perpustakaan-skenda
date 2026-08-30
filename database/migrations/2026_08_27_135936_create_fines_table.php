<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('murid_id')->constrained('murid')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->integer('late_days');
            $table->string('status', 20)->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['murid_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
