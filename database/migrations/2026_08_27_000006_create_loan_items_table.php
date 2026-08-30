<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_copy_id')->constrained()->restrictOnDelete();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_items');
    }
};
