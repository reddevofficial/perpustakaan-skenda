<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('barcode', 20)->unique();
            $table->string('status', 20)->default('available');
            $table->string('condition', 50)->default('Baik');
            $table->string('shelf_location', 50)->nullable();
            $table->timestamps();

            $table->index('barcode');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
