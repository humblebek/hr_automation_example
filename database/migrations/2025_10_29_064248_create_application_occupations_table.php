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
        Schema::create('application_occupations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('occupation_id');
            $table->unsignedBigInteger('status_id');

            $table->foreign('application_id')
                ->references('id')
                ->on('applications')->cascadeOnDelete();

            $table->foreign('occupation_id')
                ->references('id')
                ->on('occupations')->nullOnDelete();

            $table->foreign('status_id')
                ->references('id')
                ->on('statuses')->nullOnDelete();

            $table->unique(['application_id', 'occupation_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_occupations');
    }
};
