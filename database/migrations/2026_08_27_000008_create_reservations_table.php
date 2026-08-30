<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number', 30)->unique();
            $table->foreignId('murid_id')->constrained('murid')->restrictOnDelete();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('waiting');
            $table->timestamp('reserved_at');
            $table->timestamp('expires_at')->nullable();
            $table->integer('queue_position');
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('reservation_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
