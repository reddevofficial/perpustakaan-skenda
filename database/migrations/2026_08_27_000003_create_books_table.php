<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('title');
            $table->string('author');
            $table->string('publisher');
            $table->smallInteger('publication_year');
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->text('description')->nullable();
            $table->string('cover')->nullable();
            $table->string('shelf_location', 50)->nullable();
            $table->timestamps();

            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
