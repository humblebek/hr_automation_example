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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_id');
            $table->string('full_name',255);
            $table->string('phone');
            $table->text('image');
            $table->date('birth_date');
            $table->integer('sex');
            $table->unsignedBigInteger('region_id');
            $table->unsignedBigInteger('district_id');
            $table->integer('nationality');
            $table->string('selected_lang')->default(\App\Enum\Lang::UZ);
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('source_id')->nullable();

            $table->foreign('region_id')
                ->references('id')
                ->on('regions')->restrictOnDelete();
            $table->foreign('district_id')
                ->references('id')
                ->on('districts')->restrictOnDelete();
            $table->foreign('status_id')
                ->references('id')
                ->on('statuses')->restrictOnDelete();
            $table->foreign('source_id')
                ->references('id')
                ->on('sources')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
