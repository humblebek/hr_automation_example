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
        Schema::create('occupation_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('occupation_id');
            $table->string('lang_code');
            $table->string('title');
            $table->text('description');

            $table->foreign('occupation_id')
                ->references('id')
                ->on('occupations')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupation_translations');
    }
};
