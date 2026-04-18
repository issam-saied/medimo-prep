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
        Schema::create('administrations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('status', ['given', 'missed', 'refused']);
            $table->dateTime('administered_at');
            $table->string('note')->nullable();

            $table->timestamps();

            $table->index('prescription_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administrations');
    }
};
